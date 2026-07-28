<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// User-Agent giống trình duyệt thật — nhiều WAF/CDN (Cloudflare, Imperva...) chặn
// User-Agent mặc định của WordPress nhưng vẫn cho qua request dạng trình duyệt.
function whp_bct_http_args( $extra = [] ) {
    return array_merge( [
        'timeout'    => 8,
        'redirection' => 5,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
        'headers'    => [ 'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/*,*/*;q=0.8' ],
    ], $extra );
}

// Ảnh badge chính thức (file thật do người dùng cung cấp, không tự thiết kế).
function whp_bct_default_logo_url( $status = null ) {
    return MB_WHP_URL . 'assets/images/logo-bct-default.png';
}

// ─── SUBTAB "BỘ CÔNG THƯƠNG" — KIỂM TRA LIÊN KẾT ─────────────────────────────

add_action( 'wp_ajax_whp_bct_check_link', 'whp_ajax_bct_check_link' );
function whp_ajax_bct_check_link() {
    if ( ! check_ajax_referer( 'whp_bct_admin', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => __( 'Phiên làm việc hết hạn, vui lòng tải lại trang.', 'whp' ) ], 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Không có quyền.', 'whp' ) ], 403 );
    }

    $link = isset( $_POST['link'] ) ? esc_url_raw( wp_unslash( $_POST['link'] ) ) : '';
    if ( empty( $link ) ) {
        wp_send_json_error( [ 'message' => __( 'Đường dẫn không hợp lệ.', 'whp' ) ] );
    }

    $host = wp_parse_url( $link, PHP_URL_HOST );
    if ( ! whp_bct_is_official_host( $host ) ) {
        wp_send_json_error( [ 'message' => __( 'Đường dẫn không hợp lệ — phải là liên kết xác thực từ online.gov.vn.', 'whp' ) ] );
    }

    $resp = whp_bct_safe_remote_get( $link );
    if ( is_wp_error( $resp ) ) {
        wp_send_json_error( [ 'message' => __( 'Không thể truy cập đường dẫn.', 'whp' ) ] );
    }

    $code = wp_remote_retrieve_response_code( $resp );
    if ( ! in_array( $code, [ 200, 301, 302 ], true ) ) {
        wp_send_json_error( [ 'message' => __( 'Website chưa được xác thực (đường dẫn trả về lỗi).', 'whp' ) ] );
    }

    $body = wp_remote_retrieve_body( $resp );

    // Liên kết online.gov.vn hợp lệ chưa đủ — phải đúng domain của website hiện tại,
    // tránh trường hợp dán nhầm (hoặc cố tình dán) liên kết đăng ký của website khác.
    if ( ! whp_bct_link_body_matches_site( $body ) ) {
        wp_send_json_error( [ 'message' => sprintf(
            /* translators: %s: tên miền website hiện tại */
            __( 'Liên kết hợp lệ nhưng không khớp với tên miền website này (%s). Vui lòng dùng đúng liên kết đăng ký của website hiện tại.', 'whp' ),
            whp_bct_current_site_host()
        ) ] );
    }

    // Best-effort: thử lấy tên website từ tiêu đề trang — online.gov.vn có thể đổi cấu trúc bất kỳ lúc nào nên không đảm bảo luôn lấy được
    $name = '';
    if ( $body && preg_match( '/<title[^>]*>([^<]+)<\/title>/i', $body, $m ) ) {
        $name = trim( wp_strip_all_tags( $m[1] ) );
    }

    wp_send_json_success( [
        'name' => $name,
    ] );
}

// ─── SUBTAB "BỘ CÔNG THƯƠNG" — LƯU CẤU HÌNH (AJAX, KHÔNG RELOAD) ─────────────

add_action( 'wp_ajax_whp_bct_save', 'whp_ajax_bct_save' );
function whp_ajax_bct_save() {
    if ( ! check_ajax_referer( 'whp_bct_admin', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => __( 'Phiên làm việc hết hạn, vui lòng tải lại trang.', 'whp' ) ], 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Không có quyền.', 'whp' ) ], 403 );
    }

    // Lưu ý: whp_bct_status KHÔNG lưu ở đây nữa — trạng thái giờ do nút "Kiểm tra"
    // (whp_bct_check_status) tự xác định và lưu ngay, tách biệt khỏi form lưu chung.
    $link          = isset( $_POST['link'] ) ? esc_url_raw( wp_unslash( $_POST['link'] ) ) : '';
    $verified_name = isset( $_POST['verified_name'] ) ? sanitize_text_field( wp_unslash( $_POST['verified_name'] ) ) : '';

    // Chặn lưu liên kết chưa được xác minh đúng domain — trước đây "Lưu cấu hình"
    // lưu vô điều kiện, khiến badge trỏ tới liên kết đăng ký của website KHÁC vẫn
    // hiển thị công khai lên trang chủ dù nút "Kiểm tra" báo không khớp.
    // Chỉ kiểm tra (tốn 1 request mạng) khi link thực sự thay đổi so với đã lưu —
    // tránh làm chậm các lần lưu chỉ để đổi cài đặt khác (màu, kích thước...).
    $link_previous = whp_get_setting( 'whp_bct_link' );
    if ( $link !== '' && $link !== $link_previous ) {
        $link_host = wp_parse_url( $link, PHP_URL_HOST );
        if ( ! whp_bct_is_official_host( $link_host ) ) {
            wp_send_json_error( [ 'message' => __( 'Liên kết không hợp lệ — phải là liên kết xác thực từ online.gov.vn. Vui lòng bấm "Kiểm tra" trước khi lưu.', 'whp' ) ] );
        }
        $link_resp = whp_bct_safe_remote_get( $link );
        if (
            is_wp_error( $link_resp )
            || ! in_array( wp_remote_retrieve_response_code( $link_resp ), [ 200, 301, 302 ], true )
            || ! whp_bct_link_body_matches_site( wp_remote_retrieve_body( $link_resp ) )
        ) {
            wp_send_json_error( [ 'message' => sprintf(
                /* translators: %s: tên miền website hiện tại */
                __( 'Không thể lưu — liên kết không khớp với tên miền website này (%s) hoặc chưa xác thực được. Vui lòng bấm "Kiểm tra" để xác nhận trước khi lưu.', 'whp' ),
                whp_bct_current_site_host()
            ) ] );
        }
    }

    $logo_mode = isset( $_POST['logo_mode'] ) ? sanitize_key( wp_unslash( $_POST['logo_mode'] ) ) : 'auto';
    if ( ! in_array( $logo_mode, [ 'auto', 'upload' ], true ) ) {
        $logo_mode = 'auto';
    }
    $logo_url = isset( $_POST['logo_url'] ) ? esc_url_raw( wp_unslash( $_POST['logo_url'] ) ) : '';

    $bool = function ( $key ) {
        return ( isset( $_POST[ $key ] ) && $_POST[ $key ] === '1' ) ? '1' : '0';
    };
    $int = function ( $key, $default ) {
        return isset( $_POST[ $key ] ) && $_POST[ $key ] !== '' ? (string) max( 0, intval( $_POST[ $key ] ) ) : $default;
    };

    $align = isset( $_POST['align'] ) ? sanitize_key( wp_unslash( $_POST['align'] ) ) : 'center';
    if ( ! in_array( $align, [ 'left', 'center', 'right' ], true ) ) {
        $align = 'center';
    }

    $custom_html = isset( $_POST['custom_html'] ) ? wp_kses_post( wp_unslash( $_POST['custom_html'] ) ) : '';

    $setting                          = get_option( 'whp_setting', [] );
    $setting['whp_bct_link']          = $link;
    $setting['whp_bct_verified_name'] = $verified_name;
    $setting['whp_bct_logo_mode']     = $logo_mode;
    $setting['whp_bct_logo_url']      = $logo_url;
    $setting['whp_bct_pos_shortcode'] = $bool( 'pos_shortcode' );
    $setting['whp_bct_align']         = $align;
    $setting['whp_bct_width']         = $int( 'width', '130' );
    $setting['whp_bct_max_width']     = $int( 'max_width', '200' );
    $setting['whp_bct_margin_top']    = $int( 'margin_top', '20' );
    $setting['whp_bct_margin_bottom'] = $int( 'margin_bottom', '20' );
    $setting['whp_bct_fx_hover_scale']  = $bool( 'fx_hover_scale' );
    $setting['whp_bct_fx_hover_shadow'] = $bool( 'fx_hover_shadow' );
    $setting['whp_bct_fx_rounded']      = $bool( 'fx_rounded' );
    $setting['whp_bct_fx_none']         = $bool( 'fx_none' );
    $setting['whp_bct_custom_html']   = $custom_html;
    update_option( 'whp_setting', $setting );

    wp_send_json_success();
}

// ─── SUBTAB "BỘ CÔNG THƯƠNG" — KIỂM TRA TRẠNG THÁI (LINK ƯU TIÊN, FALLBACK QUÉT TRANG CHỦ) ─

add_action( 'wp_ajax_whp_bct_check_status', 'whp_ajax_bct_check_status' );
function whp_ajax_bct_check_status() {
    if ( ! check_ajax_referer( 'whp_bct_admin', 'nonce', false ) ) {
        wp_send_json_error( [ 'message' => __( 'Phiên làm việc hết hạn, vui lòng tải lại trang.', 'whp' ) ], 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( [ 'message' => __( 'Không có quyền.', 'whp' ) ], 403 );
    }

    $link_override = isset( $_POST['link'] ) ? esc_url_raw( wp_unslash( $_POST['link'] ) ) : '';
    $result        = whp_bct_run_combined_check( $link_override );

    wp_send_json_success( $result );
}

// ─── SUBTAB "BỘ CÔNG THƯƠNG" — HIỂN THỊ BADGE (HOOK + SHORTCODE) ─────────────

function whp_bct_default_html_template() {
    return '<a href="%LINK%" target="_blank" rel="noopener noreferrer"><img src="%LOGO%" alt="' . esc_attr__( 'Đã đăng ký/thông báo Bộ Công Thương', 'whp' ) . '"></a>';
}

function whp_bct_setting_or_default( $key, $default ) {
    $val = whp_get_setting( $key );
    return ( $val === '' || $val === null ) ? $default : $val;
}

function whp_bct_render_badge_html() {
    if ( whp_bct_get_status() === '' ) {
        return '';
    }

    $link = whp_get_setting( 'whp_bct_link' ) ?: 'https://online.gov.vn';
    $logo = whp_get_setting( 'whp_bct_logo_url' ) ?: whp_bct_default_logo_url();

    $custom_html = whp_get_setting( 'whp_bct_custom_html' );
    $template    = ( is_string( $custom_html ) && trim( $custom_html ) !== '' ) ? $custom_html : whp_bct_default_html_template();
    $inner       = str_replace( [ '%LINK%', '%LOGO%' ], [ esc_url( $link ), esc_url( $logo ) ], $template );

    $align   = whp_bct_setting_or_default( 'whp_bct_align', 'center' );
    $justify = $align === 'left' ? 'flex-start' : ( $align === 'right' ? 'flex-end' : 'center' );

    $width         = intval( whp_bct_setting_or_default( 'whp_bct_width', '130' ) );
    $max_width     = intval( whp_bct_setting_or_default( 'whp_bct_max_width', '200' ) );
    $margin_top    = intval( whp_bct_setting_or_default( 'whp_bct_margin_top', '20' ) );
    $margin_bottom = intval( whp_bct_setting_or_default( 'whp_bct_margin_bottom', '20' ) );

    $fx_none   = whp_get_setting( 'whp_bct_fx_none' ) == '1';
    $fx_scale  = ! $fx_none && whp_bct_setting_or_default( 'whp_bct_fx_hover_scale', '1' ) == '1';
    $fx_shadow = ! $fx_none && whp_bct_setting_or_default( 'whp_bct_fx_hover_shadow', '1' ) == '1';
    $fx_round  = ! $fx_none && whp_bct_setting_or_default( 'whp_bct_fx_rounded', '1' ) == '1';

    static $instance = 0;
    $instance++;
    $uid = 'whp-bct-badge-' . $instance;

    $css  = ".{$uid}{display:flex;justify-content:{$justify};margin:{$margin_top}px 0 {$margin_bottom}px;}";
    $css .= ".{$uid} img{width:{$width}px;max-width:{$max_width}px;height:auto;transition:transform .2s,box-shadow .2s;}";
    if ( $fx_round )  { $css .= ".{$uid} img{border-radius:8px;}"; }
    if ( $fx_scale )  { $css .= ".{$uid}:hover img{transform:scale(1.06);}"; }
    if ( $fx_shadow ) { $css .= ".{$uid}:hover img{box-shadow:0 6px 18px rgba(0,0,0,.18);}"; }

    return "<style>{$css}</style><div class=\"{$uid}\">{$inner}</div>";
}

add_shortcode( 'whp_bct', 'whp_bct_shortcode' );
function whp_bct_shortcode() {
    return whp_bct_render_badge_html();
}
