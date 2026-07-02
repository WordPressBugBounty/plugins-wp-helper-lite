<?php
defined('ABSPATH') || exit;

// Get and validate the order
$order_id = absint(get_query_var('order-received'));
$order    = $order_id ? wc_get_order($order_id) : null;

if (!$order) {
    wp_redirect(wc_get_page_permalink('shop'));
    exit;
}

// Collect all thankyou settings at once
$settings = [];
foreach (whp_get_woo_thankyou_fields() as $field) {
    $settings[$field] = whp_get_setting($field);
}

$layout        = $settings['whp_woo_thankyou_layout'] ?: 'classic';
$order_status  = $order->get_status();

$color_presets = ['blue' => '#3b82f6', 'green' => '#22c55e', 'orange' => '#f97316', 'purple' => '#9333ea', 'red' => '#ef4444', 'dark' => '#1f2937'];
$color_key     = $settings['whp_woo_thankyou_color'] ?: 'purple';
if ($color_key === 'custom') {
    $accent_color = $settings['whp_woo_thankyou_color_custom'] ?: '#9333ea';
} else {
    $accent_color = $color_presets[$color_key] ?? '#9333ea';
}

// Resolve wallet info inline so the template has no class dependency
$wallet_info = null;
$method_id   = $order->get_payment_method();
$wallet_map  = [
    'MB_WHP_Wallet_MoMo'      => ['label' => 'MoMo',      'acct' => 'account_number', 'name' => 'account_name',  'qr' => 'image_url'],
    'MB_WHP_Wallet_ZaloPay'   => ['label' => 'ZaloPay',   'acct' => 'number_zalopay', 'name' => 'name_zalopay',  'qr' => 'zalopay_image_url'],
    'MB_WHP_Wallet_VNPAY'     => ['label' => 'VNPAY',     'acct' => 'number_vnpay',   'name' => 'name_vnpay',    'qr' => 'vnpay_image_url'],
    'MB_WHP_Wallet_ShopeePay' => ['label' => 'ShopeePay', 'acct' => 'number_shopee',  'name' => 'name_shopee',   'qr' => 'shopeepay_image_url'],
];
if (isset($wallet_map[$method_id]) && function_exists('WC')) {
    $m  = $wallet_map[$method_id];
    $gw = WC()->payment_gateways()->payment_gateways()[$method_id] ?? null;
    if ($gw) {
        $acct_no   = $gw->get_option($m['acct']);
        $acct_name = $gw->get_option($m['name']);
        $qr_saved  = $gw->get_option($m['qr']);
        $tf_content = 'DH' . $order->get_order_number();


        $wallet_info = [
            'label'            => $m['label'],
            'method_id'        => $method_id,
            'number'           => $acct_no,
            'name'             => $acct_name,
            'qr_url'           => $qr_saved,
            'transfer_content' => $tf_content,
        ];
    }
}

// COD / cheque detection — show timeline instead of payment-specific cards
$is_cod    = ($method_id === 'cod');
$is_cheque = ($method_id === 'cheque');

// BACS (Direct Bank Transfer) — build wallet_info from WC gateway accounts (no QR)
if ( is_null($wallet_info) && $method_id === 'bacs' && function_exists('WC') ) {
    $bacs_accounts = get_option( 'woocommerce_bacs_accounts', [] );
    if ( !empty($bacs_accounts) && is_array($bacs_accounts) ) {
        $ba = $bacs_accounts[0];
        $wallet_info = [
            'label'            => !empty($ba['bank_name']) ? $ba['bank_name'] : __('Ngân hàng', 'whp'),
            'method_id'        => 'bacs',
            'number'           => $ba['account_number'] ?? '',
            'name'             => $ba['account_name']   ?? '',
            'qr_url'           => '',
            'transfer_content' => 'DH' . $order->get_order_number(),
        ];
    }
}

// Countdown expiry timestamp (already stored in order meta by the cron scheduler)
$expire_at = 0;
if (!empty($settings['whp_woo_thankyou_countdown_enable']) && $wallet_info && $order->has_status('pending')) {
    $expire_at = (int) $order->get_meta('_whp_payment_expires');
}

$layout_file = MB_WHP_PATH_VIEW . 'frontend/thankyou/layout-' . sanitize_key($layout) . '.php';
if (!file_exists($layout_file)) {
    $layout_file = MB_WHP_PATH_VIEW . 'frontend/thankyou/layout-classic.php';
}

// ---- Design token maps -------------------------------------------------------
$ty_font_map = [
    'system'     => "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif",
    'inter'      => "'Inter',sans-serif",
    'roboto'     => "'Roboto',sans-serif",
    'be-vietnam' => "'Be Vietnam Pro',sans-serif",
    'montserrat' => "'Montserrat',sans-serif",
];
$ty_shadow_map = [
    'none' => 'none',
    'sm'   => '0 1px 3px rgba(15,23,42,.06)',
    'md'   => '0 4px 16px rgba(15,23,42,.08)',
    'lg'   => '0 12px 32px rgba(15,23,42,.14)',
];
$ty_pad_map = [ 'compact' => '14px', 'normal' => '20px', 'relaxed' => '28px' ];
$ty_gap_map = [ 'compact' => '12px', 'normal'  => '18px', 'relaxed' => '24px' ];

$ty_font_key    = $settings['whp_woo_thankyou_font']    ?: 'inter';
$ty_radius_raw  = $settings['whp_woo_thankyou_radius'];
$ty_radius_px   = (is_numeric($ty_radius_raw) ? (string)(int)$ty_radius_raw : '12') . 'px';
$ty_shadow_key  = $settings['whp_woo_thankyou_shadow']  ?: 'md';
$ty_spacing_key = $settings['whp_woo_thankyou_spacing'] ?: 'relaxed';

$ty_font_stack  = $ty_font_map[$ty_font_key]    ?? $ty_font_map['inter'];
$ty_shadow_val  = $ty_shadow_map[$ty_shadow_key] ?? $ty_shadow_map['md'];
$ty_pad_val     = $ty_pad_map[$ty_spacing_key]   ?? $ty_pad_map['relaxed'];
$ty_gap_val     = $ty_gap_map[$ty_spacing_key]   ?? $ty_gap_map['relaxed'];

$ty_accent2_raw = $settings['whp_woo_thankyou_color2'] ?? '';
$ty_accent2     = ($ty_accent2_raw !== '' && $ty_accent2_raw !== null) ? (string)$ty_accent2_raw : '#f3f0ff';
$ty_bg_raw      = $settings['whp_woo_thankyou_bg'] ?? '';
$ty_bg          = ($ty_bg_raw !== '' && $ty_bg_raw !== null) ? (string)$ty_bg_raw : '#ffffff';

$ty_inline_style = implode(';', [
    '--whp-accent:'  . $accent_color,
    '--whp-accent2:' . $ty_accent2,
    '--whp-bg:'      . $ty_bg,
    '--whp-radius:'  . $ty_radius_px,
    '--whp-shadow:'  . $ty_shadow_val,
    '--whp-font:'    . $ty_font_stack,
    '--whp-pad:'     . $ty_pad_val,
    '--whp-gap:'     . $ty_gap_val,
]);

// Enqueue Google Font into <head> for any layout that needs it
$ty_gf_map = [
    'inter'      => 'Inter:wght@400;500;600;700',
    'roboto'     => 'Roboto:wght@400;500;700',
    'be-vietnam' => 'Be+Vietnam+Pro:wght@400;500;600;700',
    'montserrat' => 'Montserrat:wght@400;500;600;700',
];
if ( isset( $ty_gf_map[ $ty_font_key ] ) ) {
    $ty_gf_url = 'https://fonts.googleapis.com/css2?family=' . $ty_gf_map[ $ty_font_key ] . '&display=swap';
    add_action( 'wp_head', function () use ( $ty_gf_url ) {
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="stylesheet" href="' . esc_url( $ty_gf_url ) . '">' . "\n";
    }, 1 );
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php do_action( 'wp_body_open' ); ?>
<div class="whp-thankyou-wrap" style="<?php echo esc_attr( $ty_inline_style ); ?>">
    <?php include $layout_file; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
