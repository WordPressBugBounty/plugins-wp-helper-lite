<?php
if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// 2. Logic Kiểm tra các vấn đề bảo mật
function wpaap_get_security_issues()
{
    $issues = array();
    $score = 100;

    $whp_setting   = get_option('whp_setting', []);
    $extention_url = admin_url('admin.php?page=mb-wphelper-extention&subtab=security');
    $opt_on        = function($key) use ($whp_setting) {
        $v = $whp_setting[$key] ?? '';
        return $v === '1' || $v === 1 || $v === true;
    };

    // 1. Kiểm tra username 'admin'
    if (username_exists('admin')) {
        $issues[] = array(
            'level' => 'danger',
            'title' => __('Tài khoản "admin" đang tồn tại', 'whp'),
            'desc'  => __('Việc sử dụng tên đăng nhập mặc định "admin" khiến hacker dễ dàng dò mật khẩu (Brute-force attack). Bạn nên tạo một tài khoản quản trị viên mới với tên khác, cấp quyền Admin, sau đó xóa tài khoản "admin" hiện tại.', 'whp')
        );
        $score -= 20;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Không sử dụng tài khoản "admin"', 'whp'),
            'desc'  => __('Bạn đã bảo vệ website khỏi các đợt tấn công dò quét tài khoản mặc định (Brute-force).', 'whp')
        );
    }

    // 2. Kiểm tra WP_DEBUG
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $issues[] = array(
            'level' => 'danger',
            'title' => __('Chế độ gỡ lỗi (WP_DEBUG) đang BẬT', 'whp'),
            'desc'  => __('Khi WP_DEBUG được bật trên môi trường thực tế (Live site), các lỗi PHP có thể rò rỉ đường dẫn máy chủ và thông tin nhạy cảm. Hãy chuyển thành <code>define("WP_DEBUG", false);</code> trong tệp wp-config.php.', 'whp')
        );
        $score -= 20;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Đã tắt chế độ gỡ lỗi (WP_DEBUG)', 'whp'),
            'desc'  => __('Website của bạn không bị rò rỉ thông tin mã nguồn và cấu trúc thư mục ra bên ngoài.', 'whp')
        );
    }

    // 3. Kiểm tra SSL (HTTPS)
    if (! is_ssl()) {
        $issues[] = array(
            'level' => 'warning',
            'title' => __('Chưa cấu hình chứng chỉ SSL (HTTPS)', 'whp'),
            'desc'  => __('Truyền tải dữ liệu đang không được mã hóa. Hacker có thể đánh cắp thông tin đăng nhập của bạn. Hãy cài đặt chứng chỉ SSL ngay lập tức.', 'whp')
        );
        $score -= 15;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Kết nối bảo mật HTTPS (SSL) đang hoạt động', 'whp'),
            'desc'  => __('Dữ liệu truyền tải giữa người dùng và máy chủ đã được mã hóa an toàn.', 'whp')
        );
    }

    // 4. Kiểm tra phiên bản PHP
    $php_version = phpversion();
    if (version_compare($php_version, '7.4', '<')) {
        $issues[] = array(
            'level' => 'warning',
            'title' => sprintf(__('Phiên bản PHP quá cũ (%s)', 'whp'), $php_version),
            'desc'  => __('Phiên bản PHP này đã ngừng hỗ trợ vá lỗi bảo mật từ rất lâu. Vui lòng nâng cấp lên PHP 7.4 hoặc 8.x.', 'whp')
        );
        $score -= 15;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => sprintf(__('Phiên bản PHP an toàn (%s)', 'whp'), $php_version),
            'desc'  => __('Bạn đang sử dụng phiên bản PHP được hỗ trợ tốt, đảm bảo cả hiệu suất và bảo mật.', 'whp')
        );
    }

    // 5. Phiên bản WordPress
    global $wp_version;
    if (version_compare($wp_version, '6.0', '<')) {
        $issues[] = array(
            'level' => 'warning',
            'title' => sprintf(__('Phiên bản WordPress đã cũ (%s)', 'whp'), $wp_version),
            'desc'  => __('Nên cập nhật WordPress lên phiên bản mới nhất để nhận các bản vá lỗi bảo mật quan trọng.', 'whp')
        );
        $score -= 10;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => sprintf(__('Phiên bản WordPress an toàn (%s)', 'whp'), $wp_version),
            'desc'  => __('Mã nguồn lõi đang ở trạng thái mới và an toàn.', 'whp')
        );
    }

    // 6. Kiểm tra Ẩn menu Theme/Plugin & DISALLOW_FILE_EDIT
    $plugin_hides_editor = $opt_on('whp_security_hide_theme_plugin');
    $wp_config_disallows  = defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT;

    if (! $plugin_hides_editor && ! $wp_config_disallows) {
        $issues[] = array(
            'level' => 'warning',
            'title' => __('Trình chỉnh sửa tệp tin Theme/Plugin đang mở', 'whp'),
            'desc'  => sprintf(__('Nếu hacker chiếm quyền admin, họ có thể sửa trực tiếp tệp tin PHP qua giao diện. Hãy <a href="%s" style="color:#2271b1;font-weight:600;">bật "Ẩn menu Theme/Plugin"</a> trong WP Helper, hoặc thêm <code>define("DISALLOW_FILE_EDIT", true);</code> vào tệp wp-config.php.', 'whp'), esc_url($extention_url . '#card-whp_security_hide_theme_plugin'))
        );
        $score -= 10;
    } else {
        $via = $plugin_hides_editor ? __(' qua WP Helper', 'whp') : __(' qua wp-config.php', 'whp');
        $issues[] = array(
            'level' => 'success',
            'title' => __('Đã khóa trình chỉnh sửa tệp tin nội bộ', 'whp'),
            'desc'  => sprintf(__('Trình sửa tệp tin mã nguồn trong wp-admin đã được khóa%s để hạn chế rủi ro.', 'whp'), $via)
        );
    }

    // 7. Kiểm tra đường dẫn đăng nhập (dùng tính năng plugin WP Helper)
    $has_custom_login = $opt_on('whp_security_change_login_url');
    $new_login_slug   = trim($whp_setting['whp_new_login_url'] ?? '');

    if (! $has_custom_login || empty($new_login_slug)) {
        $issues[] = array(
            'level' => 'warning',
            'title' => __('Chưa đổi đường dẫn đăng nhập (wp-login.php / wp-admin)', 'whp'),
            'desc'  => sprintf(__('Các đường dẫn mặc định như <code>/wp-login.php</code>, <code>/wp-admin</code> khiến website dễ bị hacker và bot dò quét mật khẩu (Brute-force). Hãy <a href="%s" style="color:#2271b1;font-weight:600;">bật "Thay đổi đường dẫn đăng nhập"</a> ngay trong WP Helper để đổi sang một đường dẫn bí mật.', 'whp'), esc_url($extention_url . '#card-whp_security_change_login_url'))
        );
        $score -= 15;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Đã ẩn đường dẫn đăng nhập mặc định', 'whp'),
            'desc'  => __('Đường dẫn đăng nhập đã được thay bằng URL bí mật qua WP Helper, giúp chặn đứng các đợt tấn công dò quét tự động.', 'whp')
        );
    }

    // 8. Kiểm tra XML-RPC
    if (! $opt_on('whp_security_remove_xmlrpc')) {
        $issues[] = array(
            'level' => 'warning',
            'title' => __('XML-RPC chưa bị vô hiệu hóa', 'whp'),
            'desc'  => sprintf(__('XML-RPC cho phép truy cập WordPress từ xa và thường bị lợi dụng để tấn công Brute-force hoặc DDoS amplification. Hãy <a href="%s" style="color:#2271b1;font-weight:600;">bật "Vô hiệu hóa XML-RPC"</a> trong WP Helper để khóa endpoint này.', 'whp'), esc_url($extention_url . '#card-whp_security_remove_xmlrpc'))
        );
        $score -= 5;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Đã vô hiệu hóa XML-RPC', 'whp'),
            'desc'  => __('Giao thức XML-RPC đã bị khóa qua WP Helper, giảm thiểu nguy cơ tấn công Brute-force và DDoS qua endpoint này.', 'whp')
        );
    }

    // 9. Kiểm tra ẩn phiên bản WordPress
    if (! $opt_on('whp_security_hide_wp_version')) {
        $issues[] = array(
            'level' => 'warning',
            'title' => __('Phiên bản WordPress đang bị lộ ra ngoài', 'whp'),
            'desc'  => sprintf(__('Phiên bản WordPress hiển thị trong HTML source giúp hacker xác định đúng lỗ hổng bảo mật cụ thể. Hãy <a href="%s" style="color:#2271b1;font-weight:600;">bật "Ẩn phiên bản WordPress"</a> trong WP Helper để che giấu thông tin này.', 'whp'), esc_url($extention_url . '#card-whp_security_hide_wp_version'))
        );
        $score -= 5;
    } else {
        $issues[] = array(
            'level' => 'success',
            'title' => __('Đã ẩn phiên bản WordPress', 'whp'),
            'desc'  => __('Phiên bản WordPress không còn hiển thị trong HTML source, giảm thiểu thông tin mà hacker có thể thu thập.', 'whp')
        );
    }

    return array(
        'score' => max(0, $score),
        'items' => $issues
    );
}

// 3. Giao diện trang Bảo mật (Security Page Layout)
function wpaap_security_page_layout()
{
    $sec_ai_ok = false;
    if ( function_exists( 'wpaap_is_provider_connected' ) ) {
        foreach ( [ 'google', 'anthropic', 'openai' ] as $_p ) {
            if ( wpaap_is_provider_connected( $_p ) ) { $sec_ai_ok = true; break; }
        }
    } else {
        $sec_ai_ok = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
    }
    $is_connected = $sec_ai_ok;
    $has_core_api = function_exists( 'wp_ai_client_prompt' ) || function_exists( 'wp_ai_generate_text' );

    $security_data = wpaap_get_security_issues();
    $score = $security_data['score'];
    $issues = $security_data['items'];

    // Tính toán màu sắc dựa trên điểm số
    $score_color = '#10b981'; $score_bg_light = '#f0fdf4'; $score_border_light = '#bbf7d0'; $score_text_dark = '#065f46';
    if ($score < 60) {
        $score_color = '#ef4444'; $score_bg_light = '#fff5f5'; $score_border_light = '#fecaca'; $score_text_dark = '#7f1d1d';
    } elseif ($score < 80) {
        $score_color = '#f59e0b'; $score_bg_light = '#fffbeb'; $score_border_light = '#fde68a'; $score_text_dark = '#78350f';
    }
?>
    <style>
    .wpaap-security-header {
        position: relative;
        background: linear-gradient(100deg, #ffffff 0%, #fff1f2 45%, #ffe4e6 100%);
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(220,38,38,0.1), 0 0 0 1px #fecaca;
        margin-bottom: 25px;
        overflow: hidden;
        min-height: 168px;
        display: flex;
        align-items: stretch;
    }
    .wpaap-security-header-left {
        position: relative; z-index: 2;
        padding: 32px 36px;
        display: flex; flex-direction: column; justify-content: center; gap: 14px;
        max-width: 500px; flex-shrink: 0;
    }
    .wpaap-security-header-title-row { display: flex; align-items: center; gap: 14px; }
    .wpaap-security-header-icon-box {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #dc2626, #f87171);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; box-shadow: 0 4px 12px rgba(220,38,38,0.3);
    }
    .wpaap-security-header-right {
        position: absolute; inset: 0 0 0 38%;
        overflow: hidden; pointer-events: none;
    }
    </style>
    <div class="wrap wpaap-wrap" style="margin: 20px auto 40px; padding: 0 20px;">

        <div class="wpaap-security-header">
            <div class="wpaap-security-header-left">
                <div class="wpaap-security-header-title-row">
                    <div class="wpaap-security-header-icon-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    </div>
                    <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Gợi ý Bảo mật Website (Security Advisor)', 'whp'); ?></h1>
                </div>
                <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e('Quét và kiểm tra toàn diện website, phát hiện lỗ hổng bảo mật, cấu hình lỗi và đề xuất các biện pháp khắc phục.', 'whp'); ?></p>
                <div style="padding-left:58px;display:flex;align-items:center;gap:15px;">
                    <button id="wpaap_ai_scan_btn" class="wpaap-btn-danger-gradient" data-ai-ok="<?php echo $sec_ai_ok ? '1' : '0'; ?>">
                        <span class="dashicons dashicons-superhero" style="font-size:15px;width:15px;height:15px;line-height:15px;vertical-align:middle;margin-right:5px;"></span> <?php esc_html_e('AI Phân Tích Bảo Mật', 'whp'); ?>
                    </button>
                </div>
                <div id="wpaap_sec_ai_notice" style="display:none;margin:10px 0 0 58px;padding:10px 16px;border-radius:8px;font-size:13px;font-weight:600;"></div>
            </div>
            <div class="wpaap-security-header-right">
                <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                    <defs>
                        <linearGradient id="sec_grad1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#dc2626" stop-opacity="0.15"/><stop offset="100%" stop-color="#f87171" stop-opacity="0.07"/></linearGradient>
                        <linearGradient id="sec_grad2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#dc2626" stop-opacity="0.25"/><stop offset="100%" stop-color="#f87171" stop-opacity="0.12"/></linearGradient>
                    </defs>
                    <!-- Large shield center -->
                    <path d="M490 20 L540 36 L540 90 Q540 120 490 138 Q440 120 440 90 L440 36 Z" fill="url(#sec_grad2)" stroke="#fecaca" stroke-width="1.5"/>
                    <polyline points="472 82 483 93 510 66" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.6" fill="none"/>
                    <!-- Scan radar circles -->
                    <circle cx="490" cy="84" r="30" fill="none" stroke="#f87171" stroke-width="1" stroke-opacity="0.3" stroke-dasharray="4 3"/>
                    <circle cx="490" cy="84" r="50" fill="none" stroke="#f87171" stroke-width="1" stroke-opacity="0.2" stroke-dasharray="4 3"/>
                    <circle cx="490" cy="84" r="70" fill="none" stroke="#fecaca" stroke-width="1" stroke-opacity="0.15" stroke-dasharray="3 4"/>
                    <!-- Lock icon left -->
                    <rect x="320" y="58" width="36" height="28" rx="5" fill="url(#sec_grad2)" stroke="#fecaca" stroke-width="1.5"/>
                    <path d="M328 58 Q328 44 338 44 Q348 44 348 58" fill="none" stroke="#f87171" stroke-width="1.5" stroke-opacity="0.6"/>
                    <circle cx="338" cy="72" r="4" fill="#dc2626" fill-opacity="0.4"/>
                    <!-- Warning triangle -->
                    <path d="M380 110 L400 76 L420 110 Z" fill="url(#sec_grad1)" stroke="#fecaca" stroke-width="1.5"/>
                    <text x="400" y="105" text-anchor="middle" font-size="12" font-weight="700" fill="#dc2626" fill-opacity="0.6">!</text>
                    <!-- Bug with X -->
                    <circle cx="580" cy="60" r="18" fill="url(#sec_grad1)" stroke="#fecaca" stroke-width="1.5"/>
                    <line x1="572" y1="52" x2="588" y2="68" stroke="#dc2626" stroke-width="2" stroke-opacity="0.5" stroke-linecap="round"/>
                    <line x1="588" y1="52" x2="572" y2="68" stroke="#dc2626" stroke-width="2" stroke-opacity="0.5" stroke-linecap="round"/>
                    <!-- Small lock -->
                    <rect x="560" y="100" width="26" height="20" rx="4" fill="url(#sec_grad2)" stroke="#fecaca" stroke-width="1"/>
                    <path d="M566 100 Q566 92 573 92 Q580 92 580 100" fill="none" stroke="#f87171" stroke-width="1.2" stroke-opacity="0.6"/>
                    <!-- Decorative dots -->
                    <circle cx="620" cy="40" r="5" fill="#f87171" fill-opacity="0.25"/>
                    <circle cx="645" cy="120" r="7" fill="#dc2626" fill-opacity="0.15"/>
                    <circle cx="355" cy="138" r="4" fill="#f87171" fill-opacity="0.2"/>
                </svg>
            </div>
        </div>
        <hr class="wp-header-end">
        <?php if ( ! $is_connected ) : ?>
            <div class="wpaap-card-modern" style="border-top: 4px solid #d63638; padding: 24px; text-align: center;">
                <span class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px; color: #d63638; margin-bottom: 15px;"></span>
                <h2 style="margin: 0 0 10px 0; color: #d63638;"><?php esc_html_e('Chưa kết nối AI', 'whp'); ?></h2>
                <p style="font-size: 14px; color: #646970; max-width: 500px; margin: 0 auto 20px auto; line-height: 1.5;">
                    <?php esc_html_e('Bạn cần cấu hình mã khóa API trong phần Kết nối AI trước khi sử dụng tính năng này.', 'whp'); ?>
                </p>
                <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=connection'); ?>" class="wpaap-btn-primary-gradient" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; border-radius: 8px;">
                    <span class="dashicons dashicons-admin-plugins"></span> <?php esc_html_e('Sang trang Kết nối AI', 'whp'); ?>
                </a>
            </div>
        <?php else : ?>


        <style>
            /* CSS Tùy chỉnh cho nội dung AI trả về để nhìn hiện đại như SaaS */
            #wpaap_ai_advice_content h4 {
                font-size: 17px;
                color: #1d2327;
                margin: 25px 0 10px 0;
                padding-bottom: 8px;
                border-bottom: 2px solid #f0f0f1;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            #wpaap_ai_advice_content h4:first-child {
                margin-top: 0;
            }

            #wpaap_ai_advice_content p {
                margin: 0 0 15px 0;
            }

            #wpaap_ai_advice_content .ai-issue-box {
                background: #fff;
                border: 1px solid #e2e4e7;
                border-left: 4px solid #d63638;
                border-radius: 6px;
                padding: 20px;
                margin-bottom: 20px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            }

            #wpaap_ai_advice_content .ai-issue-box.warning {
                border-left-color: #f0b849;
            }

            #wpaap_ai_advice_content .ai-issue-box.success {
                border-left-color: #00a32a;
            }

            #wpaap_ai_advice_content .ai-issue-box.info {
                border-left-color: #2271b1;
            }

            #wpaap_ai_advice_content .ai-fix-steps {
                background: #f0f6fc;
                border-radius: 8px;
                border: 1px dashed #b5d1ed;
                padding: 18px 20px;
                margin-top: 15px;
            }

            #wpaap_ai_advice_content .ai-fix-steps strong {
                color: #0a4b78;
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 12px;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: 700;
            }
            
            #wpaap_ai_advice_content .ai-fix-steps strong::before {
                content: "\f463";
                font-family: "dashicons";
                font-size: 18px;
                font-weight: normal;
                color: #2271b1;
            }

            #wpaap_ai_advice_content ul {
                margin: 0;
                padding-left: 20px;
            }

            #wpaap_ai_advice_content li {
                margin-bottom: 8px;
            }

            #wpaap_ai_advice_content .ai-fix-steps ul {
                padding-left: 0;
                list-style: none;
            }

            #wpaap_ai_advice_content .ai-fix-steps li {
                position: relative;
                line-height: 1.6;
                color: #2c3338;
                background: #fff;
                padding: 12px 15px 12px 38px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 2px rgba(0,0,0,0.02);
                margin-bottom: 10px;
            }
            
            #wpaap_ai_advice_content .ai-fix-steps li::before {
                content: "\f345";
                font-family: "dashicons";
                position: absolute;
                left: 10px;
                top: 13px;
                color: #2271b1;
                font-size: 18px;
            }
            
            #wpaap_ai_advice_content .ai-fix-steps p {
                background: #fff;
                padding: 12px 15px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 1px 2px rgba(0,0,0,0.02);
                margin-bottom: 10px;
            }
        </style>

        <style>
            .wpaap-modern-box {
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.04);
                border: 1px solid #eaeaea;
                overflow: hidden;
            }
            .wpaap-issue-card {
                padding: 20px;
                margin: 0 20px 15px 20px;
                border-radius: 10px;
                display: flex;
                gap: 15px;
                transition: all 0.2s ease;
                border: 1px solid #eaeaea;
            }
            .wpaap-issue-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 15px rgba(0,0,0,0.05);
            }
            .wpaap-icon-wrap {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }
        </style>

        <!-- Khung Loading AI Quét -->
        <div id="wpaap_ai_scan_loading" class="wpaap-loading-card" style="display: none; margin-bottom: 25px;">
            <div class="wpaap-loading-title">
                <span class="dashicons dashicons-admin-generic wpaap-spinning" style="font-size: 16px; width: 16px; height: 16px; margin-right: 4px; vertical-align: middle;"></span> <?php esc_html_e('AI đang tiến hành phân tích sâu hệ thống...', 'whp'); ?> <span id="wpaap_ai_loading_percent">0%</span>
            </div>
            <div class="wpaap-loading-track">
                <div class="wpaap-loading-fill"></div>
            </div>
        </div>

        <style>
        .wpaap-loading-fill { background: linear-gradient(90deg, #dc2626, #f87171) !important; }
        .wpaap-issue-collapse-btn {
            flex-shrink: 0; margin-left: auto;
            background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px;
            padding: 3px 10px; font-size: 11.5px; font-weight: 600; color: #6b7280;
            cursor: pointer; display: inline-flex; align-items: center; gap: 4px;
            transition: all 0.15s ease;
        }
        .wpaap-issue-collapse-btn:hover { background: #e5e7eb; color: #374151; }
        .wpaap-issue-body { overflow: hidden; transition: max-height 0.3s ease, opacity 0.3s ease; }
        .wpaap-issue-body.collapsed { max-height: 0 !important; opacity: 0; }
        </style>

        <div id="wpaap_ai_advice_container" style="display: none; margin-top: 0; margin-bottom: 25px; background: #fff; border: 1px solid #e2e4e7; border-top: 4px solid #ec1c24; padding: 0; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden;">
            <div style="background: #fff5f5; padding: 16px 22px; border-bottom: 1px solid #e2e4e7; display: flex; align-items: center; gap: 10px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ec1c24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                <h3 style="margin: 0; color: #1d2327; font-size: 18px; font-weight: 600; flex: 1;"><?php esc_html_e('Báo Cáo Phân Tích & Giải Pháp Từ AI', 'whp'); ?></h3>
                <button id="wpaap_report_collapse_all" style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:4px 12px;font-size:12px;font-weight:600;color:#dc2626;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>
                    <?php esc_html_e('Thu gọn tất cả', 'whp'); ?>
                </button>
            </div>

            <div id="wpaap_ai_advice_content" style="padding: 24px 22px; font-size: 15px; color: #3c434a; line-height: 1.6;">
                <!-- Nội dung AI sẽ được đổ vào đây -->
            </div>
        </div>

        <div id="wpaap_security_main_content" style="margin-top: 25px; display: flex; gap: 25px; flex-wrap: wrap; align-items: flex-start;">

            <div class="wpaap-modern-box" style="flex: 1; min-width: 280px; align-self: flex-start; position: sticky; top: 32px; background: linear-gradient(160deg, #ffffff 0%, <?php echo esc_attr($score_bg_light); ?> 100%); border-top: 4px solid <?php echo esc_attr($score_color); ?>; overflow: hidden;">
                <?php
                if ($score >= 85)      { $level_label = __('Xuất sắc', 'whp');  $level_color = '#16a34a'; }
                elseif ($score >= 60)  { $level_label = __('Mức khá', 'whp');   $level_color = '#d97706'; }
                else                   { $level_label = __('Nguy hiểm', 'whp'); $level_color = '#dc2626'; }

                $passed_count = 0; $warning_count = 0;
                foreach ($issues as $iss) {
                    if ($iss['level'] === 'success') $passed_count++; else $warning_count++;
                }

                $cat_data = [
                    ['label' => __('Bảo mật hệ thống', 'whp'),       'score' => 100, 'checks' => [0 => 35, 1 => 35, 2 => 30]],
                    ['label' => __('Bảo mật plugin & theme', 'whp'),  'score' => 100, 'checks' => [5 => 50, 8 => 50]],
                    ['label' => __('Cấu hình máy chủ', 'whp'),        'score' => 100, 'checks' => [3 => 50, 4 => 50]],
                    ['label' => __('Bảo vệ dữ liệu', 'whp'),          'score' => 100, 'checks' => [6 => 60, 7 => 40]],
                ];
                foreach ($cat_data as &$cat) {
                    foreach ($cat['checks'] as $idx => $deduct) {
                        if (isset($issues[$idx]) && $issues[$idx]['level'] !== 'success') {
                            $cat['score'] -= $deduct;
                        }
                    }
                }
                unset($cat);
                ?>

                <!-- Domain badge -->
                <div style="padding: 18px 22px 0; display: flex; justify-content: center;">
                    <div style="background: <?php echo esc_attr($score_bg_light); ?>; padding: 5px 14px; border-radius: 20px; font-size: 12px; color: <?php echo esc_attr($score_color); ?>; font-weight: 600; border: 1px solid <?php echo esc_attr($score_border_light); ?>; display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 7px; height: 7px; background: <?php echo esc_attr($score_color); ?>; border-radius: 50%; flex-shrink: 0;"></span>
                        <?php echo esc_html(parse_url(site_url(), PHP_URL_HOST)); ?>
                    </div>
                </div>

                <!-- Title -->
                <div style="padding: 14px 22px 0; text-align: center;">
                    <h3 style="margin: 0 0 4px 0; font-size: 15px; color: #1d2327; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; justify-content: center; gap: 7px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($score_color); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg> <?php esc_html_e('Điểm Số Bảo Mật', 'whp'); ?>
                    </h3>
                    <p style="margin: 0; font-size: 11.5px; color: #9ca3af;"><?php esc_html_e('Thang điểm đánh giá dựa trên tiêu chuẩn bảo mật của WordPress', 'whp'); ?></p>
                </div>

                <!-- Score arc gauge -->
                <?php
                $sec_gauge_r     = 68;
                $sec_gauge_cx    = 100;
                $sec_gauge_cy    = 95;
                $sec_gauge_circ  = 2 * M_PI * $sec_gauge_r;
                $sec_gauge_track = $sec_gauge_circ * 0.75;
                $sec_gauge_gap   = $sec_gauge_circ * 0.25;
                $sec_gauge_fill  = $sec_gauge_track * ($score / 100);
                ?>
                <div style="display:flex;flex-direction:column;align-items:center;padding:10px 22px 0;">
                    <svg viewBox="0 0 200 165" width="200" height="165" style="overflow:visible;">
                        <circle
                            cx="<?php echo $sec_gauge_cx; ?>"
                            cy="<?php echo $sec_gauge_cy; ?>"
                            r="<?php echo $sec_gauge_r; ?>"
                            fill="none"
                            stroke="#e2e8f0"
                            stroke-width="11"
                            stroke-linecap="round"
                            stroke-dasharray="<?php echo round($sec_gauge_track, 2); ?> <?php echo round($sec_gauge_gap, 2); ?>"
                            transform="rotate(135 <?php echo $sec_gauge_cx; ?> <?php echo $sec_gauge_cy; ?>)"
                        />
                        <circle
                            cx="<?php echo $sec_gauge_cx; ?>"
                            cy="<?php echo $sec_gauge_cy; ?>"
                            r="<?php echo $sec_gauge_r; ?>"
                            fill="none"
                            stroke="<?php echo esc_attr($score_color); ?>"
                            stroke-width="11"
                            stroke-linecap="round"
                            stroke-dasharray="<?php echo round($sec_gauge_fill, 2); ?> 10000"
                            transform="rotate(135 <?php echo $sec_gauge_cx; ?> <?php echo $sec_gauge_cy; ?>)"
                        />
                        <text x="<?php echo $sec_gauge_cx; ?>" y="<?php echo $sec_gauge_cy + 16; ?>" text-anchor="middle" font-size="46" font-weight="800" fill="<?php echo esc_attr($score_color); ?>" font-family="-apple-system,sans-serif"><?php echo esc_html($score); ?></text>
                        <text x="<?php echo $sec_gauge_cx; ?>" y="<?php echo $sec_gauge_cy + 34; ?>" text-anchor="middle" font-size="14" fill="#94a3b8" font-family="-apple-system,sans-serif">/100</text>
                    </svg>
                    <div style="margin-top:-20px;font-size:18px;font-weight:800;color:<?php echo esc_attr($level_color); ?>;"><?php echo esc_html($level_label); ?></div>
                </div>

                <!-- Stats row -->
                <div style="padding: 14px 22px; display: flex; justify-content: center; gap: 18px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 34px; height: 34px; background: #f0fdf4; border-radius: 9px; display: flex; align-items: center; justify-content: center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 18px; font-weight: 700; color: #16a34a; line-height: 1.1;"><?php echo $passed_count; ?></div>
                            <div style="font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e('An toàn', 'whp'); ?></div>
                        </div>
                    </div>
                    <div style="width: 1px; background: #e5e7eb;"></div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 34px; height: 34px; background: #fffbeb; border-radius: 9px; display: flex; align-items: center; justify-content: center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        </div>
                        <div>
                            <div style="font-size: 18px; font-weight: 700; color: #dc2626; line-height: 1.1;"><?php echo $warning_count; ?></div>
                            <div style="font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;"><?php esc_html_e('Cảnh báo', 'whp'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Score breakdown bars -->
                <div style="border-top: 1px solid #f3f4f6; padding: 14px 22px;">
                    <div style="font-size: 12px; font-weight: 700; color: #374151; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg> <?php esc_html_e('Phân bố điểm số', 'whp'); ?>
                    </div>
                    <?php foreach ($cat_data as $cat):
                        $bc = $cat['score'] >= 75 ? '#16a34a' : ($cat['score'] >= 50 ? '#d97706' : '#dc2626');
                    ?>
                    <div style="margin-bottom: 9px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-size: 11.5px; color: #6b7280;"><?php echo esc_html($cat['label']); ?></span>
                            <span style="font-size: 11px; font-weight: 700; color: <?php echo esc_attr($bc); ?>;"><?php echo $cat['score']; ?>/100</span>
                        </div>
                        <div style="height: 6px; background: #f3f4f6; border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: <?php echo $cat['score']; ?>%; background: <?php echo esc_attr($bc); ?>; border-radius: 3px;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Scan info -->
                <div style="border-top: 1px solid #f3f4f6; padding: 10px 22px;">
                    <?php
                    $svg_calendar = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
                    $svg_list     = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1" fill="#dc2626"/><circle cx="3" cy="12" r="1" fill="#dc2626"/><circle cx="3" cy="18" r="1" fill="#dc2626"/></svg>';
                    $svg_check    = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                    $scan_rows = [
                        [$svg_calendar, __('Lần quét gần nhất', 'whp'), current_time('d/m/Y H:i'),                                       '#374151'],
                        [$svg_list,     __('Số tiêu chí', 'whp'),       count($issues) . ' ' . __('mục', 'whp'),                         '#374151'],
                        [$svg_check,    __('Trạng thái', 'whp'),        __('Hoàn tất', 'whp'),                                            '#16a34a'],
                    ];
                    foreach ($scan_rows as [$ico_svg, $lbl, $val, $vc]): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 5px 0; font-size: 12px; border-bottom: 1px solid #f9fafb;">
                        <span style="color: #9ca3af; display: flex; align-items: center; gap: 6px;">
                            <?php echo $ico_svg; ?>
                            <?php echo esc_html($lbl); ?>
                        </span>
                        <span style="font-weight: 600; color: <?php echo esc_attr($vc); ?>; font-size: 12px;"><?php echo esc_html($val); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Rescan button -->
                <div style="padding: 12px 22px;">
                    <button id="wpaap_rescan_btn"
                       style="width:100%; cursor:pointer; border:none; display: flex; align-items: center; justify-content: center; gap: 7px; background: linear-gradient(135deg, #ec1c24, #f37021); color: #fff; padding: 10px; border-radius: 8px; font-size: 13px; font-weight: 600; box-shadow: 0 4px 10px rgba(236,28,36,0.2); transition: all 0.3s ease;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> <?php esc_html_e('Quét lại ngay', 'whp'); ?>
                    </button>
                </div>

                <!-- Quick tip -->
                <div style="margin: 0 16px 18px; background: linear-gradient(135deg, #ff8b66c4 0%, #792104 100%); border-radius: 10px; padding: 14px 16px; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -12px; right: -12px; width: 70px; height: 70px; background: rgba(255,255,255,0.07); border-radius: 50%;"></div>
                    <div style="position: absolute; bottom: -18px; right: 18px; width: 50px; height: 50px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
                    <div style="position: relative; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 7px;">
                            <div style="width: 26px; height: 26px; background: rgba(255,255,255,0.2); border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                            </div>
                            <strong style="font-size: 12.5px; font-weight: 700; color: #fff;"><?php esc_html_e('Bảo vệ website', 'whp'); ?></strong>
                        </div>
                        <p style="margin: 0; font-size: 11.5px; color: rgba(255,255,255,0.85); line-height: 1.5;"><?php esc_html_e('Bật các tính năng bảo vệ cơ bản để website luôn trong tình trạng an toàn.', 'whp'); ?></p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-extention&subtab=security')); ?>"
                           style="display: inline-flex; align-items: center; gap: 5px; background: #fff; color: #dc2626; text-decoration: none; padding: 6px 12px; border-radius: 6px; font-size: 11.5px; font-weight: 700; align-self: flex-start; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                            <?php esc_html_e('Cấu hình ngay', 'whp'); ?>
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="wpaap-modern-box" style="flex: 2; min-width: 400px; padding: 0; overflow: hidden;">
                <div style="padding: 15px 20px; background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%); border-bottom: 1px solid #fecaca; display: flex; align-items: center; justify-content: space-between;">
                    <h3 style="margin: 0; font-size: 17px; display: flex; align-items: center; gap: 10px; color: #1d2327; font-weight: 700;">
                        <div style="width: 34px; height: 34px; background: linear-gradient(135deg, #dc2626, #f87171); border-radius: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 8px rgba(220,38,38,0.25);">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                        </div>
                        <?php esc_html_e('Danh sách kiểm tra bảo mật chi tiết', 'whp'); ?>
                    </h3>
                    <span style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 3px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;"><?php printf(esc_html__('Tổng cộng: %d mục', 'whp'), count($issues)); ?></span>
                </div>
                <div style="padding: 8px 0;">
                    <?php foreach ($issues as $issue) : ?>
                        <?php
                        if ($issue['level'] === 'danger') {
                            $icon = 'dashicons-warning'; $icon_color = '#dc2626'; $icon_bg = '#fef2f2';
                            $badge = __('Nguy hiểm', 'whp'); $badge_bg = '#fef2f2'; $badge_color = '#dc2626'; $badge_border = '#fecaca';
                        } elseif ($issue['level'] === 'warning') {
                            $icon = 'dashicons-flag'; $icon_color = '#d97706'; $icon_bg = '#fffbeb';
                            $badge = __('Cảnh báo', 'whp'); $badge_bg = '#fffbeb'; $badge_color = '#d97706'; $badge_border = '#fde68a';
                        } else {
                            $icon = 'dashicons-yes-alt'; $icon_color = '#16a34a'; $icon_bg = '#f0fdf4';
                            $badge = __('An toàn', 'whp'); $badge_bg = '#f0fdf4'; $badge_color = '#16a34a'; $badge_border = '#bbf7d0';
                        }
                        ?>
                        <div class="wpaap-issue-card" style="background: #fff; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                            <div style="display: flex; gap: 12px; align-items: flex-start; flex: 1; min-width: 0;">
                                <div class="wpaap-icon-wrap" style="background: <?php echo esc_attr($icon_bg); ?>; flex-shrink: 0;">
                                    <span class="dashicons <?php echo esc_attr($icon); ?>" style="color: <?php echo esc_attr($icon_color); ?>; font-size: 18px; width: 18px; height: 18px;"></span>
                                </div>
                                <div style="min-width: 0;">
                                    <h4 style="margin: 0 0 4px 0; font-size: 14px; color: #1d2327; font-weight: 600; line-height: 1.4;"><?php echo esc_html($issue['title']); ?></h4>
                                    <p style="margin: 0; font-size: 13px; color: #6b7280; line-height: 1.5;"><?php echo wp_kses_post($issue['desc']); ?></p>
                                </div>
                            </div>
                            <span style="flex-shrink: 0; background: <?php echo esc_attr($badge_bg); ?>; color: <?php echo esc_attr($badge_color); ?>; border: 1px solid <?php echo esc_attr($badge_border); ?>; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; margin-top: 2px;"><?php echo esc_html($badge); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- Feature cards -->
        <div style="margin-top: 24px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;">
            <?php
            $features = [
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>',
                    'color' => '#dc2626', 'bg' => '#fef2f2',
                    'title' => __('Bảo vệ toàn diện', 'whp'),
                    'desc'  => __('Bật XML-RPC, ẩn WP version, login URL bí mật và các tính năng bảo vệ tích hợp sẵn.', 'whp'),
                    'cta'   => __('Cài đặt tính năng', 'whp'),
                    'href'  => admin_url('admin.php?page=mb-wphelper-extention&subtab=security'),
                    'js'    => '',
                ],
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>',
                    'color' => '#2563eb', 'bg' => '#eff6ff',
                    'title' => __('Phát hiện kịp thời', 'whp'),
                    'desc'  => __('9 tiêu chí tự động phát hiện lỗ hổng, cấu hình sai và các rủi ro bảo mật.', 'whp'),
                    'cta'   => __('Xem danh sách kiểm tra', 'whp'),
                    'href'  => '#wpaap_security_main_content',
                    'js'    => "document.getElementById('wpaap_security_main_content').scrollIntoView({behavior:'smooth'});return false;",
                ],
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
                    'color' => '#16a34a', 'bg' => '#f0fdf4',
                    'title' => __('Hướng dẫn chi tiết', 'whp'),
                    'desc'  => __('AI phân tích chuyên sâu và đưa ra giải pháp khắc phục cụ thể từng vấn đề.', 'whp'),
                    'cta'   => __('Chạy AI Phân tích', 'whp'),
                    'href'  => '#',
                    'js'    => "jQuery('html,body').animate({scrollTop:0},400,function(){document.getElementById('wpaap_ai_scan_btn').click();});return false;",
                ],
                [
                    'svg'   => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
                    'color' => '#7c3aed', 'bg' => '#f5f3ff',
                    'title' => __('Tự động hóa AI', 'whp'),
                    'desc'  => __('Kết nối Gemini / Claude / GPT để AI tự động phân tích và đề xuất khắc phục.', 'whp'),
                    'cta'   => __('Cấu hình kết nối AI', 'whp'),
                    'href'  => admin_url('admin.php?page=mb-wphelper-ai&subtab=connection'),
                    'js'    => '',
                ],
            ];
            foreach ($features as $feat): ?>
            <div style="background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; padding: 20px 18px; display: flex; flex-direction: column; gap: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                <div style="width: 40px; height: 40px; background: <?php echo esc_attr($feat['bg']); ?>; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                    <?php echo $feat['svg']; ?>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 14px; font-weight: 700; color: #1d2327; margin-bottom: 4px;"><?php echo esc_html($feat['title']); ?></div>
                    <div style="font-size: 12.5px; color: #6b7280; line-height: 1.5;"><?php echo esc_html($feat['desc']); ?></div>
                </div>
                <a href="<?php echo esc_url($feat['href']); ?>"
                   <?php if ($feat['js']): ?>onclick="<?php echo esc_attr($feat['js']); ?>"<?php endif; ?>
                   style="font-size: 12px; color: <?php echo esc_attr($feat['color']); ?>; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                    <?php echo esc_html($feat['cta']); ?> →
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Mẹo tip bar -->
        <div style="margin-top: 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 12px 18px; display: flex; align-items: center; gap: 10px; font-size: 13px; color: #1e40af;">
            <span class="dashicons dashicons-info" style="color: #3b82f6; font-size: 18px; width: 18px; height: 18px; flex-shrink: 0;"></span>
            <span><strong><?php esc_html_e('Mẹo:', 'whp'); ?></strong> <?php esc_html_e('Hãy kiểm tra bảo mật định kỳ để đảm bảo website luôn được bảo vệ an toàn trước mọi đe dọa mới.', 'whp'); ?></span>
        </div>

        <?php endif; ?>
    </div>

    <script>
    var whpSecAiI18n = {
        connecting:  '<?php echo esc_js(__('Đang kết nối AI...', 'whp')); ?>',
        reanalyze:   '<?php echo esc_js(__('AI Phân Tích Lại', 'whp')); ?>',
        collapseAll: '<?php echo esc_js(__('Thu gọn tất cả', 'whp')); ?>',
        expandAll:   '<?php echo esc_js(__('Mở rộng tất cả', 'whp')); ?>',
        collapse:    '<?php echo esc_js(__('Thu gọn', 'whp')); ?>',
        expand:      '<?php echo esc_js(__('Mở rộng', 'whp')); ?>'
    };
        jQuery(document).ready(function($) {
            $('#wpaap_ai_scan_btn').on('click', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $notice = $('#wpaap_sec_ai_notice');

                if ($btn.data('ai-ok') !== 1 && $btn.data('ai-ok') !== '1') {
                    $notice.addClass('mb-error').css({'background':'#fef2f2','color':'#dc2626','border':'1px solid #fecaca'})
                        .html('⚠️ Chưa kết nối AI. Vui lòng cấu hình API Key tại <a href="?page=mb-wphelper-ai&subtab=connection" style="color:#dc2626;text-decoration:underline;">tab Kết nối AI</a> trước.')
                        .show();
                    setTimeout(function() { $notice.fadeOut(); }, 8000);
                    return;
                }

                // UI thay đổi
                $btn.prop('disabled', true).css('opacity', '0.7').html('<span class="dashicons dashicons-update-alt" style="animation: wpaapSpin 2s infinite linear;"></span> ' + whpSecAiI18n.connecting);
                $('#wpaap_ai_scan_loading').slideDown();
                $('#wpaap_ai_advice_container').slideUp();

                // Thêm keyframes quay icon
                if ($('#wpaap-spin-css').length === 0) {
                    $('head').append('<style id="wpaap-spin-css">@keyframes wpaapSpin { 100% { transform: rotate(360deg); } }</style>');
                }

                // Logic chạy % ảo trong lúc đợi
                var percent = 0;
                $('#wpaap_ai_loading_percent').text('0%');
                var interval = setInterval(function() {
                    if (percent < 99) {
                        percent += Math.floor(Math.random() * 10) + 1;
                        if (percent > 99) percent = 99;
                        $('#wpaap_ai_loading_percent').text(percent + '%');
                    }
                }, 600);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'wpaap_ai_security_scan',
                        nonce: '<?php echo wp_create_nonce("wpaap_security_nonce"); ?>'
                    },
                    success: function(response) {
                        clearInterval(interval);
                        $('#wpaap_ai_loading_percent').text('100%');

                        $btn.prop('disabled', false).css('opacity', '1').html('<span class="dashicons dashicons-superhero" style="font-size:15px;width:15px;height:15px;line-height:15px;vertical-align:middle;margin-right:5px;"></span> ' + whpSecAiI18n.reanalyze);
                        $('#wpaap_ai_scan_loading').slideUp();

                        if (response.success && response.data.ai_advice) {
                            $('#wpaap_ai_advice_content').html(response.data.ai_advice);
                            $('#wpaap_ai_advice_container').slideDown(400, function() {
                                wpaapInjectCollapseButtons();
                                wpaapInjectSecurityCardLinks();
                                _allCollapsed = false;
                                $('#wpaap_report_collapse_all').html('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> ' + whpSecAiI18n.collapseAll);
                            });
                        } else {
                            var errMsg = (response.data && response.data.message) ? response.data.message : (response.data || 'Không nhận được dữ liệu hợp lệ.');
                            alert('Có lỗi xảy ra: ' + errMsg);
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        clearInterval(interval);

                        $btn.prop('disabled', false).css('opacity', '1').html('<span class="dashicons dashicons-superhero" style="font-size:15px;width:15px;height:15px;line-height:15px;vertical-align:middle;margin-right:5px;"></span> AI Phân Tích Bảo Mật');
                        $('#wpaap_ai_scan_loading').slideUp();
                        
                        var errorMsg = 'Lỗi kết nối máy chủ! Quá trình phản hồi AI có thể đã vượt quá thời gian cho phép.';
                        if (xhr.status) {
                            errorMsg += '\nMã lỗi HTTP: ' + xhr.status + ' ' + errorThrown;
                        }
                        if (xhr.responseText) {
                            errorMsg += '\nChi tiết: ' + xhr.responseText.substring(0, 500);
                        }
                        alert(errorMsg);
                    }
                });
            });

            // Nút "Quét lại ngay": scroll to top rồi trigger scan
            $('#wpaap_rescan_btn').on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 400, function() {
                    $('#wpaap_ai_scan_btn').trigger('click');
                });
            });

            // Inject nút thu gọn vào từng ai-issue-box sau khi AI content load
            function wpaapInjectCollapseButtons() {
                $('#wpaap_ai_advice_content .ai-issue-box').each(function(i) {
                    var $box = $(this);
                    if ($box.find('.wpaap-issue-collapse-btn').length) return;

                    // Detect severity từ class
                    var badgeColor = '#dc2626', badgeBg = '#fef2f2', badgeBorder = '#fecaca', badgeText = 'Nguy hiểm';
                    if ($box.hasClass('warning')) {
                        badgeColor = '#d97706'; badgeBg = '#fffbeb'; badgeBorder = '#fde68a'; badgeText = 'Cảnh báo';
                    } else if ($box.hasClass('info')) {
                        badgeColor = '#2563eb'; badgeBg = '#eff6ff'; badgeBorder = '#bfdbfe'; badgeText = 'Khuyến nghị';
                    } else if ($box.hasClass('success')) {
                        badgeColor = '#16a34a'; badgeBg = '#f0fdf4'; badgeBorder = '#bbf7d0'; badgeText = 'Ổn định';
                    }

                    // Wrap body content
                    var $h4 = $box.find('h4').first();
                    var $body = $box.children().not('h4');
                    $body.wrapAll('<div class="wpaap-issue-body" style="max-height:2000px;opacity:1;"></div>');

                    // Badge màu + nút thu gọn vào h4
                    var badgeHtml = '<span style="flex-shrink:0;background:' + badgeBg + ';color:' + badgeColor + ';border:1px solid ' + badgeBorder + ';padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;">' + badgeText + '</span>';
                    var btnHtml = '<button class="wpaap-issue-collapse-btn" data-state="open" style="margin-left:auto;">' +
                        '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>' +
                        whpSecAiI18n.collapse + '</button>';
                    $h4.css({'display':'flex','justify-content':'flex-start','align-items':'center','gap':'8px'}).append(badgeHtml + btnHtml);
                });

                // Click từng nút
                $('#wpaap_ai_advice_content').off('click', '.wpaap-issue-collapse-btn').on('click', '.wpaap-issue-collapse-btn', function() {
                    var $btn = $(this);
                    var $body = $btn.closest('.ai-issue-box').find('.wpaap-issue-body');
                    var isOpen = $btn.data('state') === 'open';
                    if (isOpen) {
                        $body.addClass('collapsed');
                        $btn.data('state', 'closed').html('<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> ' + whpSecAiI18n.expand);
                    } else {
                        $body.removeClass('collapsed');
                        $btn.data('state', 'open').html('<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> ' + whpSecAiI18n.collapse);
                    }
                });
            }

            // Inject link nhanh tới đúng card bảo mật trong WP Helper
            function wpaapInjectSecurityCardLinks() {
                var baseUrl = '<?php echo esc_js(admin_url("admin.php?page=mb-wphelper-extention&subtab=security")); ?>';
                var cardMap = [
                    { keywords: ['Theme/Plugin', 'theme plugin', 'chỉnh sửa tệp', 'DISALLOW_FILE_EDIT'], card: 'card-whp_security_hide_theme_plugin', label: 'Ẩn menu Theme/Plugin' },
                    { keywords: ['đường dẫn đăng nhập', 'wp-login', 'wp-admin', 'Brute-force', 'brute force'], card: 'card-whp_security_change_login_url', label: 'Thay đổi đường dẫn đăng nhập' },
                    { keywords: ['XML-RPC', 'xmlrpc', 'DDoS amplification'], card: 'card-whp_security_remove_xmlrpc', label: 'Vô hiệu hóa XML-RPC' },
                    { keywords: ['phiên bản WordPress', 'WP version', 'lộ ra ngoài', 'lộ phiên bản'], card: 'card-whp_security_hide_wp_version', label: 'Ẩn phiên bản WordPress' },
                ];
                $('#wpaap_ai_advice_content .ai-issue-box').each(function() {
                    var $box = $(this);
                    if ($box.find('.wpaap-card-link').length) return;
                    var h4Text = $box.find('h4').first().text();
                    var $fixSteps = $box.find('.ai-fix-steps');
                    if (!$fixSteps.length) return;
                    cardMap.forEach(function(item) {
                        var matched = item.keywords.some(function(kw) {
                            return h4Text.toLowerCase().indexOf(kw.toLowerCase()) !== -1;
                        });
                        if (matched && !$box.find('.wpaap-card-link').length) {
                            $fixSteps.append(
                                '<a href="' + baseUrl + '#' + item.card + '" class="wpaap-card-link" ' +
                                'style="display:inline-flex;align-items:center;gap:6px;margin-top:12px;' +
                                'background:#dc2626;color:#fff;text-decoration:none;padding:7px 14px;' +
                                'border-radius:7px;font-size:12.5px;font-weight:700;box-shadow:0 2px 6px rgba(220,38,38,0.25);">' +
                                '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>' +
                                'Bật "' + item.label + '" ngay</a>'
                            );
                        }
                    });
                });
            }

            // Thu gọn / mở rộng tất cả
            var _allCollapsed = false;
            $('#wpaap_report_collapse_all').on('click', function() {
                var $btn = $(this);
                _allCollapsed = !_allCollapsed;
                $('#wpaap_ai_advice_content .wpaap-issue-body').toggleClass('collapsed', _allCollapsed);
                $('#wpaap_ai_advice_content .wpaap-issue-collapse-btn').each(function() {
                    $(this).data('state', _allCollapsed ? 'closed' : 'open');
                    if (_allCollapsed) {
                        $(this).html('<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> ' + whpSecAiI18n.expand);
                    } else {
                        $(this).html('<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> ' + whpSecAiI18n.collapse);
                    }
                });
                $btn.html(_allCollapsed
                    ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> ' + whpSecAiI18n.expandAll
                    : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> ' + whpSecAiI18n.collapseAll);
            });
        });
    </script>
<?php
}

// 4. Hàm xử lý AJAX gửi dữ liệu cho AI
add_action('wp_ajax_wpaap_ai_security_scan', 'wpaap_ajax_ai_security_scan_handler');
function wpaap_ajax_ai_security_scan_handler()
{
    check_ajax_referer('wpaap_security_nonce', 'nonce');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Không có quyền thực hiện hành động này.', 'whp' ) ] );
        return;
    }

    // Kiểm tra kết nối AI trước mọi thứ — không cho bypass qua try/catch
    $ai_any_connected = false;
    if ( function_exists( 'wpaap_is_provider_connected' ) ) {
        foreach ( ['google', 'anthropic', 'openai'] as $_prov ) {
            if ( wpaap_is_provider_connected( $_prov ) ) {
                $ai_any_connected = true;
                break;
            }
        }
    }
    if ( ! $ai_any_connected ) {
        wp_send_json_error( array( 'message' => __('Chưa có nhà cung cấp AI nào được kết nối. Vui lòng cấu hình API Key tại tab AI Kết nối.', 'whp') ) );
    }

    $ai_model = get_option('wpaap_default_ai_model', 'gemini-2.5-flash');

    // Tăng thời gian phản hồi PHP để chờ AI
    if (function_exists('set_time_limit')) {
        @set_time_limit(300);
    }
    @ini_set('memory_limit', '256M');

    // Ghi đè timeout của wp_remote_post
    $timeout_filter = function ($args) {
        $args['timeout'] = 300;
        return $args;
    };
    add_filter('http_request_args', $timeout_filter, 999);

    $curl_filter = function( $handle ) {
        curl_setopt( $handle, CURLOPT_TIMEOUT, 300 );
        curl_setopt( $handle, CURLOPT_CONNECTTIMEOUT, 300 );
    };
    add_action( 'http_api_curl', $curl_filter, 999 );

    try {
        // 1. Chạy lại quét để lấy dữ liệu mới nhất
        $security_data = wpaap_get_security_issues();

        // 2. Chuyển đổi dữ liệu thành prompt gửi cho AI
        $issues_text = "Website Security Report (Score: " . $security_data['score'] . "/100):\n";
        foreach ($security_data['items'] as $item) {
            $issues_text .= "- [" . strtoupper($item['level']) . "] " . $item['title'] . ": " . strip_tags($item['desc']) . "\n";
        }

        $extention_url = admin_url('admin.php?page=mb-wphelper-extention&subtab=security');
        $is_en = strpos(get_locale(), 'en') === 0;
        $lang  = $is_en ? 'English' : 'Vietnamese';

        if ($is_en) {
            $plugin_links = "
    - For \"Disable XML-RPC\": <a href=\"{$extention_url}#card-whp_security_remove_xmlrpc\" style=\"color:#2271b1;font-weight:600;\">enable \"Disable XML-RPC\" in WP Helper</a>
    - For \"Hide WordPress Version\": <a href=\"{$extention_url}#card-whp_security_hide_wp_version\" style=\"color:#2271b1;font-weight:600;\">enable \"Hide WordPress Version\" in WP Helper</a>
    - For \"Hide Theme/Plugin Editor\": <a href=\"{$extention_url}#card-whp_security_hide_theme_plugin\" style=\"color:#2271b1;font-weight:600;\">enable \"Hide Theme/Plugin Editor\" in WP Helper</a>
    - For \"Change Login URL\": <a href=\"{$extention_url}#card-whp_security_change_login_url\" style=\"color:#2271b1;font-weight:600;\">enable \"Change Login URL\" in WP Helper</a>
    Example: <li>Go to and <a href=\"URL#card-ID\" style=\"color:#2271b1;font-weight:600;\">enable \"Feature Name\" in WP Helper</a> to fix this immediately.</li>";
            $template_p1 = "
    PART 1: For EACH security issue listed in the report, output this exact HTML:
    <div class='ai-issue-box'>
        <h4><span class='dashicons dashicons-warning' style='color: #d63638; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Issue Name or Risk]</h4>
        <p>[Detailed explanation of why this issue is dangerous]</p>
        <div class='ai-fix-steps'>
            <strong>How to Fix:</strong>
            <ul>
                <li>[Step 1]</li>
                <li>[Step 2]</li>
            </ul>
        </div>
    </div>";
            $template_p2 = "
    PART 2: After addressing the report's issues, provide 2 or 3 ADDITIONAL overall/advanced WordPress security recommendations (e.g., WAF, 2FA, Backups) that were NOT mentioned in the report. Output them using this exact HTML structure with the 'info' class:
    <div class='ai-issue-box info'>
        <h4><span class='dashicons dashicons-lightbulb' style='color: #f0b849; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Advanced Security Recommendation Name]</h4>
        <p>[Explanation of why the website needs this feature for overall protection]</p>
        <div class='ai-fix-steps'>
            <strong>Implementation Guide:</strong>
            <ul>
                <li>[Step 1]</li>
                <li>[Step 2]</li>
            </ul>
        </div>
    </div>";
        } else {
            $plugin_links = "
    - For \"Vô hiệu hóa XML-RPC\": <a href=\"{$extention_url}#card-whp_security_remove_xmlrpc\" style=\"color:#2271b1;font-weight:600;\">bật \"Vô hiệu hóa XML-RPC\" trong WP Helper</a>
    - For \"Ẩn phiên bản WordPress\": <a href=\"{$extention_url}#card-whp_security_hide_wp_version\" style=\"color:#2271b1;font-weight:600;\">bật \"Ẩn phiên bản WordPress\" trong WP Helper</a>
    - For \"Ẩn menu Theme/Plugin\": <a href=\"{$extention_url}#card-whp_security_hide_theme_plugin\" style=\"color:#2271b1;font-weight:600;\">bật \"Ẩn menu Theme/Plugin\" trong WP Helper</a>
    - For \"Thay đổi đường dẫn đăng nhập\": <a href=\"{$extention_url}#card-whp_security_change_login_url\" style=\"color:#2271b1;font-weight:600;\">bật \"Thay đổi đường dẫn đăng nhập\" trong WP Helper</a>
    Example of correct usage: <li>Truy cập và <a href=\"URL#card-ID\" style=\"color:#2271b1;font-weight:600;\">bật \"Tên tính năng\" trong WP Helper</a> để khắc phục ngay.</li>";
            $template_p1 = "
    PART 1: For EACH security issue listed in the report, output this exact HTML:
    <div class='ai-issue-box'>
        <h4><span class='dashicons dashicons-warning' style='color: #d63638; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Tên lỗi hoặc Rủi ro]</h4>
        <p>[Giải thích chi tiết vì sao lỗi này lại cực kỳ nguy hiểm]</p>
        <div class='ai-fix-steps'>
            <strong>Hướng dẫn khắc phục:</strong>
            <ul>
                <li>[Bước 1]</li>
                <li>[Bước 2]</li>
            </ul>
        </div>
    </div>";
            $template_p2 = "
    PART 2: After addressing the report's issues, provide 2 or 3 ADDITIONAL overall/advanced WordPress security recommendations (e.g., WAF, 2FA, Backups) that were NOT mentioned in the report. Output them using this exact HTML structure with the 'info' class:
    <div class='ai-issue-box info'>
        <h4><span class='dashicons dashicons-lightbulb' style='color: #f0b849; font-size: 18px; width: 18px; height: 18px; margin-right: 4px; vertical-align: text-bottom;'></span> [Tên đề xuất bảo mật nâng cao]</h4>
        <p>[Giải thích vì sao website cần tính năng này để bảo vệ tổng thể]</p>
        <div class='ai-fix-steps'>
            <strong>Hướng dẫn triển khai:</strong>
            <ul>
                <li>[Bước 1]</li>
                <li>[Bước 2]</li>
            </ul>
        </div>
    </div>";
        }

        $system_prompt = "You are a Cyber Security Advisor for a WordPress site using the WP Helper plugin. Read the security report (Score: " . $security_data['score'] . "/100) and provide ALL advice strictly in {$lang}. Do NOT mix languages.

    IMPORTANT PLUGIN CONTEXT: This WordPress site already has the WP Helper plugin installed with built-in security features. When any of these features are relevant, embed a clickable inline <a> link in the fix step text (do NOT write raw URLs). Use exactly these anchor tags:
{$plugin_links}
    NEVER write raw URLs in the output. Always embed links with descriptive anchor text.

    CRITICAL: You MUST strictly output ONLY clean HTML using the exact structure below. Do NOT use markdown. Do NOT use standard plain text paragraphs.
{$template_p1}
{$template_p2}

    Report to analyze:
    " . $issues_text;

        $provider_models = [
            'google' => ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-2.0-flash-lite', 'gemini-2.0-flash-lite-001', 'gemini-2.5-flash-lite'],
            'anthropic' => ['claude-3-5-sonnet', 'claude-3-opus', 'claude-3-haiku'],
            'openai' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo']
        ];
        
        $selected_provider = '';
        foreach ( $provider_models as $prov => $mods ) {
            if ( in_array( $ai_model, $mods ) ) {
                $selected_provider = $prov;
                break;
            }
        }
        
        $models_to_try = [];
        // Chỉ thêm mô hình từ các provider đã được kết nối
        foreach ( $provider_models as $prov => $mods ) {
            if ( function_exists( 'wpaap_is_provider_connected' ) && wpaap_is_provider_connected( $prov ) ) {
                if ( $prov === $selected_provider ) {
                    array_unshift( $models_to_try, $ai_model );
                    foreach ( $mods as $m ) {
                        if ( $m !== $ai_model ) {
                            $models_to_try[] = $m;
                        }
                    }
                } else {
                    $models_to_try = array_merge( $models_to_try, $mods );
                }
            }
        }
        
        if ( empty( $models_to_try ) ) {
            wp_send_json_error( array( 'message' => __('Chưa có nhà cung cấp AI nào được kết nối. Vui lòng cấu hình API Key tại trang AI Kết nối.', 'whp') ) );
        }
        
        $models_to_try = array_values( array_unique( $models_to_try ) );

        $ai_response = '';
        $last_error_msg = '';

        foreach ($models_to_try as $model) {
            $current_response = '';
            
            // Gọi trực tiếp API
            if ( function_exists( 'wpaap_call_ai_api_direct' ) ) {
                $direct_result = wpaap_call_ai_api_direct( $model, $system_prompt );
                if ( ! is_wp_error( $direct_result ) ) {
                    $current_response = $direct_result;
                } else {
                    $last_error_msg = $direct_result->get_error_message();
                }
            } else {
                wp_send_json_error(__('Hệ thống kết nối AI trực tiếp chưa được thiết lập.', 'whp'));
            }
            
            if (!empty($current_response)) {
                $ai_response = $current_response;
                break;
            }
        }

        // Nếu tất cả model đều thất bại
        if (empty($ai_response)) {
            $user_friendly_err = $last_error_msg;
            if (strpos(strtolower($last_error_msg), '429') !== false || strpos(strtolower($last_error_msg), 'too many requests') !== false || strpos(strtolower($last_error_msg), 'quota exceeded') !== false) {
                $user_friendly_err = __('Toàn bộ tài khoản API của bạn đã hết sạch lượt truy vấn miễn phí (Quota Limit) trong chu kỳ này. Vui lòng nghỉ ngơi khoảng 1-2 phút để Google hồi phục lại số lượt dùng, hoặc chủ động chọn một Mô hình AI khác ở ô Lựa chọn Model bên trên!', 'whp');
            }
            $ai_response = "<div style='background: #fcf0f1; border-left: 4px solid #d63638; padding: 15px 20px; color: #d63638; border-radius: 4px; margin-top: 10px;'>
                                <strong style='font-size: 15px;'>" . esc_html__('Không thể hoàn thành phân tích do quá tải AI!', 'whp') . "</strong><br>
                                <span style='font-size: 14px; margin-top: 8px; display: block;'>" . esc_html__('Hệ thống đã tự động thử tất cả các Mô hình AI dự phòng (Fallback) nhưng đều bị từ chối.', 'whp') . "<br>
                                <br><strong>" . esc_html__('Gợi ý:', 'whp') . "</strong> " . esc_html($user_friendly_err) . "</span>
                            </div>";
        }

        // Đảm bảo AI Response là một chuỗi an toàn
        if (! is_string($ai_response)) {
            if (is_wp_error($ai_response)) {
                $err_msg = $ai_response->get_error_message();
                if (strpos($err_msg, '503') !== false || strpos($err_msg, 'high demand') !== false) {
                    $ai_response = "<p style='color: #d63638;'><strong>" . esc_html__('Hệ thống AI đang quá tải (Lỗi 503):', 'whp') . "</strong> " . esc_html__('Hiện tại máy chủ AI đang xử lý quá nhiều yêu cầu. Bạn vui lòng đợi khoảng 1-2 phút và bấm nút Quét lại nhé!', 'whp') . "</p>";
                } else {
                    $ai_response = "<p style='color: #d63638;'><strong>" . esc_html__('Lỗi API:', 'whp') . "</strong> " . esc_html($err_msg) . "</p>";
                }
            } else {
                $ai_response = wp_json_encode($ai_response);
            }
        }

        // Dọn dẹp markdown code blocks nếu AI trả về lỗi định dạng
        $ai_response = trim($ai_response);
        $ai_response = preg_replace('/^```(?:html)?\s*/i', '', $ai_response);
        $ai_response = preg_replace('/\s*```$/', '', $ai_response);

        if (empty($ai_response) || $ai_response === '""' || $ai_response === '[]') {
            $ai_response = "<p style='color: #d63638;'>" . esc_html__('Không thể kết nối với AI lúc này hoặc dữ liệu trả về rỗng. Vui lòng kiểm tra lại thiết lập mã khóa API trong trang Kết nối AI.', 'whp') . "</p>";
        }

        wp_send_json_success([
            'ai_advice' => wp_kses_post($ai_response)
        ]);
    } catch (\Throwable $e) {
        // Bắt lỗi hệ thống để không crash AJAX
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) { error_log( 'WPAAP AI Security Scan Error: ' . $e->getMessage() ); }
        wp_send_json_error(__('Lỗi hệ thống: ', 'whp') . $e->getMessage());
    } finally {
        // Đảm bảo gỡ filter trong mọi trường hợp
        if ( isset( $timeout_filter ) ) {
            remove_filter( 'http_request_args', $timeout_filter, 999 );
        }
        if ( isset( $curl_filter ) ) {
            remove_action( 'http_api_curl', $curl_filter, 999 );
        }
    }
}
