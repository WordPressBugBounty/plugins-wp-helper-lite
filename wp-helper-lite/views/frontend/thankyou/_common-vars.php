<?php
defined('ABSPATH') || exit;

/* Shared variable setup — included by all three layouts */

$accent_preset  = $settings['whp_woo_thankyou_color']        ?? 'blue';
$accent_custom  = $settings['whp_woo_thankyou_color_custom'] ?? '';
$show_timeline  = ! empty( $settings['whp_woo_thankyou_show_timeline'] );
// COD and cheque: always hide timeline regardless of global setting
if ( $is_cod || ( isset( $is_cheque ) && $is_cheque ) ) {
    $show_timeline = false;
}
$copy_account   = ! empty( $settings['whp_woo_thankyou_copy_account'] );
$copy_content   = ! empty( $settings['whp_woo_thankyou_copy_content'] );
$show_support   = ! empty( $settings['whp_woo_thankyou_show_support_btn'] );
$countdown_en   = ! empty( $settings['whp_woo_thankyou_countdown_enable'] );
$countdown_min  = (int) ( $settings['whp_woo_thankyou_countdown_minutes'] ?? 30 );
$btn_continue   = $settings['whp_woo_thankyou_btn_continue']   ?? '';
$btn_contact    = $settings['whp_woo_thankyou_btn_contact']    ?? '';
$btn_view_order = $settings['whp_woo_thankyou_btn_view_order'] ?? '';
$btn_invoice    = $settings['whp_woo_thankyou_btn_invoice']    ?? '';

if ( '1' === $btn_continue   ) $btn_continue   = __( 'Tiếp tục mua hàng', 'whp' );
if ( '1' === $btn_view_order ) $btn_view_order = __( 'Xem đơn hàng',       'whp' );
if ( '1' === $btn_contact    ) $btn_contact    = __( 'Liên hệ hỗ trợ',     'whp' );
if ( '1' === $btn_invoice    ) $btn_invoice    = __( 'Tải hóa đơn PDF',    'whp' );

$trust_badges  = ! empty( $settings['whp_woo_thankyou_trust_badges'] );
$show_transfer = ! empty( $settings['whp_woo_thankyou_transfer_btn'] );

$accent_map = [
    'blue'   => '#3b82f6',
    'green'  => '#10b981',
    'purple' => '#8b5cf6',
    'orange' => '#f97316',
    'red'    => '#ef4444',
    'pink'   => '#ec4899',
];
$accent = ( 'custom' === $accent_preset && '' !== trim( $accent_custom ) )
    ? sanitize_hex_color( $accent_custom )
    : ( $accent_map[ $accent_preset ] ?? '#3b82f6' );

$order_number  = $order ? $order->get_order_number() : '';
$order_status  = $order ? $order->get_status() : '';
$payment_title = $order ? $order->get_payment_method_title() : '';
$order_date    = ( $order && $order->get_date_created() )
    ? $order->get_date_created()->date_i18n( 'd/m/Y - H:i' )
    : '';

$has_wallet    = ! empty( $wallet_info );
$qr_url        = $has_wallet ? ( $wallet_info['qr_url']           ?? '' ) : '';
$acct_number   = $has_wallet ? ( $wallet_info['number']           ?? '' ) : '';
$wallet_label  = $has_wallet ? ( $wallet_info['label']            ?? '' ) : '';
$wallet_name   = $has_wallet ? ( $wallet_info['name']             ?? '' ) : '';
$transfer_cont = $has_wallet ? ( $wallet_info['transfer_content'] ?? '' ) : '';
$show_qr_large = ! empty( $settings['whp_woo_thankyou_show_qr_large'] );

$expire_at      = $order ? (int) $order->get_meta( '_whp_payment_expires' ) : 0;
$show_countdown = $countdown_en && $has_wallet && 'pending' === $order_status && $expire_at > 0;

$shop_url   = get_permalink( wc_get_page_id( 'shop' ) ) ?: home_url( '/' );
$orders_url = wc_get_account_endpoint_url( 'orders' );
$order_tracking_url = ( $order && empty( $is_admin_preview ) )
    ? wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) )
    : '';

$contact_channels = [
    'zalo'  => [ 'en' => ! empty( $settings['whp_woo_thankyou_contact_zalo_en'] ),  'val' => (string) ( $settings['whp_woo_thankyou_contact_zalo_val']  ?? '' ) ],
    'fb'    => [ 'en' => ! empty( $settings['whp_woo_thankyou_contact_fb_en'] ),    'val' => (string) ( $settings['whp_woo_thankyou_contact_fb_val']    ?? '' ) ],
    'msg'   => [ 'en' => ! empty( $settings['whp_woo_thankyou_contact_msg_en'] ),   'val' => (string) ( $settings['whp_woo_thankyou_contact_msg_val']   ?? '' ) ],
    'email' => [ 'en' => ! empty( $settings['whp_woo_thankyou_contact_email_en'] ), 'val' => (string) ( $settings['whp_woo_thankyou_contact_email_val'] ?? '' ) ],
];
$has_any_contact_channel = array_reduce( $contact_channels, fn( $c, $ch ) => $c || ( $ch['en'] && $ch['val'] !== '' ), false );

$font_key   = $settings['whp_woo_thankyou_font'] ?? 'inter';
$font_stack = [
    'system'     => "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif",
    'inter'      => "'Inter',sans-serif",
    'roboto'     => "'Roboto',sans-serif",
    'be-vietnam' => "'Be Vietnam Pro',sans-serif",
    'montserrat' => "'Montserrat',sans-serif",
];
$font_family = $font_stack[ $font_key ] ?? $font_stack['inter'];
