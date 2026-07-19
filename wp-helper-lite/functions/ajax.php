<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
function whp_ajax_sendmail_popup()
{
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'whp_popup_subscribe_nonce' ) ) {
        wp_send_json( [ 'status' => 403 ] );
    }

    // Rate limiting: tối đa 5 lần/IP/giờ
    $ip       = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
    $rate_key = 'whp_popup_rate_' . md5( $ip );
    $hits     = (int) get_transient( $rate_key );
    if ( $hits >= 5 ) {
        wp_send_json( [ 'status' => 429 ] );
    }
    set_transient( $rate_key, $hits + 1, HOUR_IN_SECONDS );

    $data = isset( $_POST['data'] ) ? (array) $_POST['data'] : [];
    if ( empty( $data[0]['value'] ) ) {
        wp_send_json( [ 'status' => 400 ] );
    }

    $email = sanitize_email( $data[0]['value'] );
    if ( ! is_email( $email ) ) {
        wp_send_json( [ 'status' => 400 ] );
    }

    $content      = whp_get_option( 'whp_popup_mail_template' );
    $content      = str_replace( [ '{email}' ], esc_html( $email ), $content );
    $to           = get_option( 'admin_email' );
    $smtp_email   = whp_get_option( 'whp_smtp_email' );
    $smtp_name    = whp_get_option( 'whp_smtp_from_name' ) ?: get_bloginfo( 'name' );
    $headers      = [ 'Content-Type: text/html; charset=UTF-8' ];
    if ( $smtp_email ) {
        $headers[] = 'From: ' . $smtp_name . ' <' . $smtp_email . '>';
    }
    $result = wp_mail( $to, 'Thông báo thành viên đăng ký mới', $content, $headers );

    wp_send_json( [ 'status' => $result ? 200 : 500 ] );
}
add_action( 'wp_ajax_whp_ajax_sendmail_popup', 'whp_ajax_sendmail_popup' );
add_action( 'wp_ajax_nopriv_whp_ajax_sendmail_popup', 'whp_ajax_sendmail_popup' );

// ─── CONTACT TOGGLE AUTO-SAVE ─────────────────────────────────────────────────

add_action( 'wp_ajax_whp_contact_toggle_active', 'whp_ajax_contact_toggle_active' );
function whp_ajax_contact_toggle_active() {
    if ( ! check_ajax_referer( 'whp_contact_toggle', 'nonce', false ) ) {
        wp_send_json_error( 'invalid_nonce', 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'forbidden', 403 );
    }
    $active  = isset( $_POST['active'] ) && $_POST['active'] === '1' ? '1' : '0';
    $setting = get_option( 'whp_setting', [] );
    $setting['whp_contact_active'] = $active;
    update_option( 'whp_setting', $setting );
    whp_purge_page_cache();
    wp_send_json_success( [ 'active' => $active ] );
}

// ─── THANKYOU PAGE TOGGLE AUTO-SAVE ──────────────────────────────────────────

add_action( 'wp_ajax_whp_thankyou_toggle_enable', 'whp_ajax_thankyou_toggle_enable' );
function whp_ajax_thankyou_toggle_enable() {
    if ( ! check_ajax_referer( 'whp_thankyou_toggle', 'nonce', false ) ) {
        wp_send_json_error( 'invalid_nonce', 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'forbidden', 403 );
    }
    $active  = isset( $_POST['active'] ) && $_POST['active'] === '1' ? '1' : '0';
    $setting = get_option( 'whp_setting', [] );
    $setting['whp_woo_thankyou_enable'] = $active;
    update_option( 'whp_setting', $setting );
    whp_purge_page_cache();
    wp_send_json_success( [ 'active' => $active ] );
}

// ─── SMTP TOGGLE AUTO-SAVE ───────────────────────────────────────────────────

add_action( 'wp_ajax_whp_smtp_toggle_enable', 'whp_ajax_smtp_toggle_enable' );
function whp_ajax_smtp_toggle_enable() {
    if ( ! check_ajax_referer( 'whp_smtp_toggle', 'nonce', false ) ) {
        wp_send_json_error( 'invalid_nonce', 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'forbidden', 403 );
    }
    $active  = isset( $_POST['active'] ) && $_POST['active'] === '1' ? '1' : '0';
    $setting = get_option( 'whp_setting', [] );
    $setting['whp_smtp_active'] = $active;
    update_option( 'whp_setting', $setting );
    whp_purge_page_cache();
    wp_send_json_success( [ 'active' => $active ] );
}

add_action( 'wp_ajax_whp_popup_toggle_enable', 'whp_ajax_popup_toggle_enable' );
function whp_ajax_popup_toggle_enable() {
    if ( ! check_ajax_referer( 'whp_popup_toggle', 'nonce', false ) ) {
        wp_send_json_error( 'invalid_nonce', 403 );
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'forbidden', 403 );
    }
    $active  = isset( $_POST['active'] ) && $_POST['active'] === '1' ? '1' : '0';
    $setting = get_option( 'whp_setting', [] );
    $setting['whp_popup_active'] = $active;
    update_option( 'whp_setting', $setting );
    whp_purge_page_cache();
    wp_send_json_success( [ 'active' => $active ] );
}
