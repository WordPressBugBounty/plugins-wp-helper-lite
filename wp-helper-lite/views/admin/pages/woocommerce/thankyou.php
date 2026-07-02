<?php defined('ABSPATH') || exit; ?>
<?php
$whp_woo_thankyou_enable           = whp_get_setting('whp_woo_thankyou_enable');
$whp_woo_thankyou_layout           = whp_get_setting('whp_woo_thankyou_layout') ?: 'classic';
$whp_woo_thankyou_color            = whp_get_setting('whp_woo_thankyou_color') ?: 'blue';
$whp_woo_thankyou_color_custom     = whp_get_setting('whp_woo_thankyou_color_custom') ?: '#3b82f6';
$_ty_c2                            = whp_get_setting('whp_woo_thankyou_color2');
$whp_woo_thankyou_color2           = ($_ty_c2 === '' || $_ty_c2 === null || $_ty_c2 === false) ? '#f3f0ff' : (string) $_ty_c2;
$_ty_bg                            = whp_get_setting('whp_woo_thankyou_bg');
$whp_woo_thankyou_bg               = ($_ty_bg === '' || $_ty_bg === null || $_ty_bg === false) ? '#ffffff' : (string) $_ty_bg;
$whp_woo_thankyou_show_timeline    = whp_get_setting('whp_woo_thankyou_show_timeline');
$whp_woo_thankyou_show_qr_large    = whp_get_setting('whp_woo_thankyou_show_qr_large');
$whp_woo_thankyou_copy_account     = whp_get_setting('whp_woo_thankyou_copy_account');
$whp_woo_thankyou_copy_content     = whp_get_setting('whp_woo_thankyou_copy_content');
$whp_woo_thankyou_show_support_btn = whp_get_setting('whp_woo_thankyou_show_support_btn');
$whp_woo_thankyou_countdown_enable = whp_get_setting('whp_woo_thankyou_countdown_enable');
$whp_woo_thankyou_countdown_minutes = whp_get_setting('whp_woo_thankyou_countdown_minutes') ?: '30';
$whp_woo_thankyou_btn_continue     = whp_get_setting('whp_woo_thankyou_btn_continue');
$whp_woo_thankyou_btn_contact      = whp_get_setting('whp_woo_thankyou_btn_contact');
$whp_woo_thankyou_btn_view_order   = whp_get_setting('whp_woo_thankyou_btn_view_order');
$whp_woo_thankyou_btn_invoice      = whp_get_setting('whp_woo_thankyou_btn_invoice');
$whp_woo_thankyou_trust_badges     = whp_get_setting('whp_woo_thankyou_trust_badges');
$whp_woo_thankyou_transfer_btn     = whp_get_setting('whp_woo_thankyou_transfer_btn');
$whp_woo_thankyou_transfer_email   = whp_get_setting('whp_woo_thankyou_transfer_email');
$whp_contact_zalo_en  = whp_get_setting('whp_woo_thankyou_contact_zalo_en');
$whp_contact_zalo_val = whp_get_setting('whp_woo_thankyou_contact_zalo_val') ?: '';
$whp_contact_fb_en    = whp_get_setting('whp_woo_thankyou_contact_fb_en');
$whp_contact_fb_val   = whp_get_setting('whp_woo_thankyou_contact_fb_val') ?: '';
$whp_contact_msg_en   = whp_get_setting('whp_woo_thankyou_contact_msg_en');
$whp_contact_msg_val  = whp_get_setting('whp_woo_thankyou_contact_msg_val') ?: '';
$whp_contact_email_en  = whp_get_setting('whp_woo_thankyou_contact_email_en');
$whp_contact_email_val = whp_get_setting('whp_woo_thankyou_contact_email_val') ?: '';

// ===== AI Tích hợp — điều kiện thực tế =====
$_ai_enable        = (bool) whp_get_setting('whp_aipay_enable');
// Đọc trạng thái kết nối từ trang AI Kết nối (central connection) thay vì key riêng cũ
$_ai_has_key       = false;
$_ai_connected_label = '';
if ( function_exists( 'wpaap_is_provider_connected' ) ) {
    foreach ( [ 'google' => 'Gemini', 'anthropic' => 'Claude', 'openai' => 'OpenAI' ] as $_p => $_lbl ) {
        if ( wpaap_is_provider_connected( $_p ) ) {
            $_ai_has_key = true;
            if ( $_ai_connected_label === '' ) $_ai_connected_label = $_lbl;
        }
    }
} else {
    // Fallback: đọc key cũ nếu hàm chưa tồn tại
    $_ai_openai_key = trim( (string) whp_get_setting( 'whp_aipay_openai_key' ) );
    $_ai_gemini_key = trim( (string) whp_get_setting( 'whp_aipay_gemini_key' ) );
    $_ai_has_key    = $_ai_openai_key !== '' || $_ai_gemini_key !== '';
    $_ai_connected_label = $_ai_openai_key !== '' ? 'OpenAI' : ( $_ai_gemini_key !== '' ? 'Gemini' : '' );
}
if ( ! isset( $_ai_openai_key ) ) $_ai_openai_key = '';
if ( ! isset( $_ai_gemini_key ) ) $_ai_gemini_key = '';

$_ai_ocr_on        = (bool) whp_get_setting('whp_aipay_ocr_enable');
$_ai_fraud_on      = (bool) whp_get_setting('whp_aipay_fraud_enable');
$_ai_copilot_on    = (bool) whp_get_setting('whp_aipay_copilot_enable');

$_ai_tg_token      = trim((string) whp_get_setting('whp_aipay_telegram_token'));
$_ai_tg_chat       = trim((string) whp_get_setting('whp_aipay_telegram_chat_id'));
$_ai_discord       = trim((string) whp_get_setting('whp_aipay_discord_webhook'));
$_ai_webhook       = trim((string) whp_get_setting('whp_aipay_webhook_url'));
$_ai_email         = trim((string) whp_get_setting('whp_aipay_email_address'));

// Badge conditions per feature
$_feat_ocr      = $_ai_enable && $_ai_ocr_on && $_ai_has_key;
$_feat_verify   = $_ai_enable && $_ai_ocr_on && $_ai_has_key;   // xác minh cần OCR + AI key
$_feat_fraud    = $_ai_enable && $_ai_fraud_on && $_ai_has_key;
$_feat_notify   = $_ai_enable && ($_ai_tg_token !== '' && $_ai_tg_chat !== '' || $_ai_discord !== '' || $_ai_webhook !== '' || $_ai_email !== '');
$_feat_copilot  = $_ai_enable && $_ai_copilot_on && $_ai_has_key;
$_ai_any_active = $_ai_enable && $_ai_has_key;

// Helper: render badge
if ( ! function_exists( 'whp_ai_badge' ) ) {
function whp_ai_badge(bool $on, string $label_on, string $label_off = '', bool $blue = false): string {
    $label_off = $label_off ?: __('Chưa bật','whp');
    if ($on) {
        $bg  = $blue ? '#dbeafe' : '#dcfce7';
        $clr = $blue ? '#1d4ed8' : '#16a34a';
        return '<span class="whp-ai-feat-badge" style="background:' . $bg . ';color:' . $clr . '">' . esc_html($label_on) . '</span>';
    }
    return '<span class="whp-ai-feat-badge" style="background:#f1f5f9;color:#94a3b8">' . esc_html($label_off) . '</span>';
}
}

// ===== Hướng dẫn sử dụng =====
$whp_guide_enable   = whp_get_setting('whp_woo_thankyou_guide_enable');
$whp_guide_style    = whp_get_setting('whp_woo_thankyou_guide_style') ?: 'accordion';
$whp_guide_btn_text = whp_get_setting('whp_woo_thankyou_guide_btn_text') ?: 'Xem hướng dẫn';

// 5 section × 3 bước
$whp_guide_sections = [
    'overview' => ['label' => 'Tổng quan',    'icon' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>'],
    'design'   => ['label' => 'Giao diện',    'icon' => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/>'],
    'payment'  => ['label' => 'Thanh toán',   'icon' => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>'],
    'advanced' => ['label' => 'Nâng cao',     'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 4.6a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 .33 1.65 1.65 0 0 0 10 1.51V2a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'],
    'ai'       => ['label' => 'AI tích hợp', 'icon' => '<path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/>'],
];
foreach ($whp_guide_sections as $slug => $_) {
    for ($i = 1; $i <= 3; $i++) {
        ${"whp_guide_{$slug}_step{$i}_title"} = whp_get_setting("whp_guide_{$slug}_step{$i}_title") ?: '';
        ${"whp_guide_{$slug}_step{$i}_desc"}  = whp_get_setting("whp_guide_{$slug}_step{$i}_desc") ?: '';
    }
}

$ty_on = (bool) $whp_woo_thankyou_enable;
$layout_names = ['classic' => 'Classic', 'modern' => 'Modern', 'premium' => 'Premium Card'];
$color_hex_map = ['blue' => '#3b82f6', 'green' => '#22c55e', 'orange' => '#f97316', 'purple' => '#9333ea', 'red' => '#ef4444', 'dark' => '#1f2937', 'custom' => $whp_woo_thankyou_color_custom ?: '#3b82f6'];
$accent = $color_hex_map[$whp_woo_thankyou_color] ?? '#3b82f6';

// QR Thanh toán phụ thuộc cổng ví điện tử — kiểm tra có ví nào đang bật không
$whp_ty_wallet_ready = false;
foreach (['whp_woocommerce_wallet_momo', 'whp_woocommerce_wallet_zalopay', 'whp_woocommerce_wallet_vnpay', 'whp_woocommerce_wallet_shopeepay'] as $wf) {
    if (whp_get_setting($wf)) { $whp_ty_wallet_ready = true; break; }
}
$whp_ty_wallet_url = admin_url('admin.php?page=mb-wphelper-woocommerce-advance&subtab=wallet');

// ===== Design tokens (Kiểu chữ / Bo góc / Bóng đổ / Khoảng cách) =====
$whp_woo_thankyou_font    = whp_get_setting('whp_woo_thankyou_font') ?: 'inter';
$_ty_rv                   = whp_get_setting('whp_woo_thankyou_radius'); // '0' (Vuông) là giá trị hợp lệ → không dùng ?:
$whp_woo_thankyou_radius  = ($_ty_rv === '' || $_ty_rv === null || $_ty_rv === false) ? '12' : (string) $_ty_rv;
$whp_woo_thankyou_shadow  = whp_get_setting('whp_woo_thankyou_shadow') ?: 'md';
$whp_woo_thankyou_spacing = whp_get_setting('whp_woo_thankyou_spacing') ?: 'relaxed';

$ty_font_opts   = ['inter' => 'Inter', 'system' => __('Mặc định hệ thống', 'whp'), 'roboto' => 'Roboto', 'be-vietnam' => 'Be Vietnam Pro', 'montserrat' => 'Montserrat'];
$ty_font_stack  = ['system' => "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif", 'inter' => "'Inter',sans-serif", 'roboto' => "'Roboto',sans-serif", 'be-vietnam' => "'Be Vietnam Pro',sans-serif", 'montserrat' => "'Montserrat',sans-serif"];
$ty_radius_opts = ['0' => __('Vuông (0px)','whp'), '8' => __('Nhỏ (8px)','whp'), '12' => __('Vừa (12px)','whp'), '16' => __('Lớn (16px)','whp'), '20' => __('Rất lớn (20px)','whp')];
$ty_shadow_opts = ['none' => __('Không','whp'), 'sm' => __('Nhẹ','whp'), 'md' => __('Trung bình','whp'), 'lg' => __('Đậm','whp')];
$ty_shadow_css  = ['none' => 'none', 'sm' => '0 1px 3px rgba(15,23,42,.08)', 'md' => '0 4px 16px rgba(15,23,42,.10)', 'lg' => '0 12px 32px rgba(15,23,42,.16)'];
$ty_spacing_opts = ['compact' => __('Gọn','whp'), 'normal' => __('Vừa','whp'), 'relaxed' => __('Rộng rãi','whp')];
$ty_pad_css     = ['compact' => '12px', 'normal' => '16px', 'relaxed' => '20px'];

$rp_font    = $ty_font_stack[$whp_woo_thankyou_font] ?? $ty_font_stack['inter'];
$rp_radius  = (string) intval($whp_woo_thankyou_radius) . 'px';
$rp_shadow  = $ty_shadow_css[$whp_woo_thankyou_shadow] ?? $ty_shadow_css['md'];
$rp_pad     = $ty_pad_css[$whp_woo_thankyou_spacing] ?? $ty_pad_css['relaxed'];
$rp_accent2 = $whp_woo_thankyou_color2 ?: '#f3f0ff';
$rp_bg      = $whp_woo_thankyou_bg ?: '#ffffff';

// Countdown khởi tạo từ số phút đã chọn
$cd_min  = (int) ($whp_woo_thankyou_countdown_minutes ?: 30);
$cd_secs = $cd_min * 60;
$cd_init = sprintf('%02d : %02d : %02d', intdiv($cd_min, 60), $cd_min % 60, 0);

// ===== Rich preview — render từ layout files thật với mock data =====
ob_start();

// 1. Settings từ DB, override bật tất cả modules
$_rp_settings = [];
if ( function_exists( 'whp_get_woo_thankyou_fields' ) ) {
    foreach ( whp_get_woo_thankyou_fields() as $_rf ) {
        $_rp_settings[ $_rf ] = whp_get_setting( $_rf );
    }
}
$_rp_settings['whp_woo_thankyou_show_timeline']    = '1';
$_rp_settings['whp_woo_thankyou_countdown_enable'] = '1';
$_rp_settings['whp_woo_thankyou_trust_badges']     = '1';
$_rp_settings['whp_woo_thankyou_transfer_btn']     = '1';
$_rp_settings['whp_woo_thankyou_btn_continue']     = '1';
$_rp_settings['whp_woo_thankyou_btn_view_order']   = '1';
$_rp_settings['whp_woo_thankyou_btn_contact']      = '1';
$_rp_settings['whp_woo_thankyou_btn_invoice']      = '1';
$_rp_settings['whp_woo_thankyou_show_support_btn'] = '1';
$_rp_settings['whp_woo_thankyou_show_qr_large']    = '0';
// Pass real channel settings vào preview (admin đã điền gì thì preview hiện đúng vậy)
$_rp_settings['whp_woo_thankyou_contact_zalo_en']   = $whp_contact_zalo_en;
$_rp_settings['whp_woo_thankyou_contact_zalo_val']  = $whp_contact_zalo_val;
$_rp_settings['whp_woo_thankyou_contact_fb_en']     = $whp_contact_fb_en;
$_rp_settings['whp_woo_thankyou_contact_fb_val']    = $whp_contact_fb_val;
$_rp_settings['whp_woo_thankyou_contact_msg_en']    = $whp_contact_msg_en;
$_rp_settings['whp_woo_thankyou_contact_msg_val']   = $whp_contact_msg_val;
$_rp_settings['whp_woo_thankyou_contact_email_en']  = $whp_contact_email_en;
$_rp_settings['whp_woo_thankyou_contact_email_val'] = $whp_contact_email_val;

// 2. Mock WC order (PHP 8.0+ anonymous class)
$_rp_order = new class {
    public function get_order_number(): string { return '12345'; }
    public function get_status(): string { return 'pending'; }
    public function get_payment_method_title(): string { return 'MB Bank'; }
    public function get_date_created() {
        return new class { public function date_i18n( string $f ): string { return '14/06/2026 - 15:30'; } };
    }
    public function get_meta( string $k ): string { return $k === '_whp_payment_expires' ? (string)( time() + 1800 ) : ''; }
    public function has_status( string $s ): bool { return $s === 'pending'; }
    public function get_total(): float { return 1250000.0; }
    public function get_items(): array { return []; }
    public function get_billing_first_name(): string { return 'Văn A'; }
    public function get_billing_last_name(): string { return 'Nguyễn'; }
    public function get_billing_address_1(): string { return '123 Đường ABC'; }
    public function get_billing_city(): string { return 'TP.HCM'; }
    public function get_billing_email(): string { return ''; }
    public function get_billing_phone(): string { return ''; }
    public function get_total_discount(): float { return 0.0; }
    public function get_shipping_total(): float { return 0.0; }
    public function get_formatted_order_total(): string { return function_exists( 'wc_price' ) ? wc_price( 1250000 ) : '1.250.000₫'; }
};

// 3. Mock wallet info
$_rp_wallet = [
    'label'            => 'MB Bank',
    'number'           => '1234 5678 9999',
    'name'             => 'NGUYEN VAN A',
    'qr_url'           => '',
    'transfer_content' => 'DH12345',
    'method_id'        => '',
];

// 4. CSS vars (giống thankyou-template.php)
$_rp_a2r  = $_rp_settings['whp_woo_thankyou_color2'] ?? '';
$_rp_a2   = ( $_rp_a2r !== '' && $_rp_a2r !== null ) ? (string)$_rp_a2r : '#f3f0ff';
$_rp_bgr  = $_rp_settings['whp_woo_thankyou_bg'] ?? '';
$_rp_bg2  = ( $_rp_bgr !== '' && $_rp_bgr !== null ) ? (string)$_rp_bgr : '#ffffff';
$_rp_fmap = [ 'system' => "-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif", 'inter' => "'Inter',sans-serif", 'roboto' => "'Roboto',sans-serif", 'be-vietnam' => "'Be Vietnam Pro',sans-serif", 'montserrat' => "'Montserrat',sans-serif" ];
$_rp_smap = [ 'none' => 'none', 'sm' => '0 1px 3px rgba(15,23,42,.06)', 'md' => '0 4px 16px rgba(15,23,42,.08)', 'lg' => '0 12px 32px rgba(15,23,42,.14)' ];
$_rp_pmap = [ 'compact' => '14px', 'normal' => '20px', 'relaxed' => '28px' ];
$_rp_gmap = [ 'compact' => '12px', 'normal' => '18px', 'relaxed' => '24px' ];
$_rp_fk   = (string)( $_rp_settings['whp_woo_thankyou_font']    ?? 'inter' );
$_rp_rrw  = $_rp_settings['whp_woo_thankyou_radius'];
$_rp_rpx  = ( is_numeric( $_rp_rrw ) ? (string)(int)$_rp_rrw : '12' ) . 'px';
$_rp_sk   = (string)( $_rp_settings['whp_woo_thankyou_shadow']  ?? 'md' );
$_rp_spk  = (string)( $_rp_settings['whp_woo_thankyou_spacing'] ?? 'relaxed' );
$_rp_inline = implode( ';', [
    '--whp-accent:'  . $accent,
    '--whp-accent2:' . $_rp_a2,
    '--whp-bg:'      . $_rp_bg2,
    '--whp-radius:'  . $_rp_rpx,
    '--whp-shadow:'  . ( $_rp_smap[ $_rp_sk ]  ?? $_rp_smap['md'] ),
    '--whp-font:'    . ( $_rp_fmap[ $_rp_fk ]  ?? $_rp_fmap['inter'] ),
    '--whp-pad:'     . ( $_rp_pmap[ $_rp_spk ] ?? $_rp_pmap['relaxed'] ),
    '--whp-gap:'     . ( $_rp_gmap[ $_rp_spk ] ?? $_rp_gmap['relaxed'] ),
] );

// 5. Render cả 3 layouts, show layout đang chọn
$_rp_active       = sanitize_key( $whp_woo_thankyou_layout ?: 'classic' );
$is_admin_preview = true;
?>
<div class="whp-thankyou-wrap" style="<?php echo esc_attr( $_rp_inline ); ?>">
<?php foreach ( [ 'classic', 'modern', 'premium' ] as $_rp_lk ) :
    $_rp_lf   = MB_WHP_PATH_VIEW . 'frontend/thankyou/layout-' . $_rp_lk . '.php';
    $settings    = $_rp_settings;
    $order       = $_rp_order;
    $wallet_info = $_rp_wallet;
    $is_cod      = false;
    $expire_at   = time() + 1800;
?>
<div data-rp-layout="<?php echo esc_attr( $_rp_lk ); ?>" style="display:<?php echo $_rp_lk === $_rp_active ? 'block' : 'none'; ?>">
<?php if ( file_exists( $_rp_lf ) ) include $_rp_lf; ?>
</div>
<?php endforeach;
unset( $is_admin_preview, $settings, $order, $wallet_info, $is_cod, $expire_at );
?>
</div>
<?php
$rp_markup = ob_get_clean();
?>
<!-- whp-ty-v2026-06-13 -->
<style>
/* Hide WP admin page title */
#wpcontent .wrap > h1 { display:none !important; }

/* ===== TAB ICON ===== */
.whp-ty-tab { display:inline-flex; align-items:center; gap:7px; }
.whp-ty-tab svg { width:15px; height:15px; flex-shrink:0; }

/* ===== TOGGLE Bật/Tắt LABEL ===== */
.whp-ty-toggle-wrap { display:inline-flex; align-items:center; gap:8px; flex-shrink:0; }
.whp-ty-toggle-txt { font-size:12px; font-weight:700; color:#64748b; padding:3px 9px; border-radius:20px; min-width:auto; text-align:center; }
.whp-ty-toggle-txt.on { color:#16a34a; }
/* pill badge riêng cho lưới Overview — nền xanh đặc khi bật */
.whp-ov-module .whp-ty-toggle-txt { font-size:11px; }
.whp-ov-module .whp-ty-toggle-txt.on { color:#22c55e; }

/* ===== SETTING ROW (Kiểu chữ / Bo góc / Bóng đổ / Khoảng cách) ===== */
.whp-ty-setrow {
    display:flex; align-items:center; gap:12px;
    padding:13px 16px; border:1.5px solid #e8edf3; border-radius:10px;
    background:#fff; margin-top:10px; transition:border-color .15s;
}
.whp-ty-setrow:hover { border-color:#fecdd3; }
.whp-ty-setrow-icon { width:34px; height:34px; border-radius:9px; background:#fff1f2; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-ty-setrow-label { flex:1; font-size:13.5px; font-weight:600; color:#0f172a; }
.whp-ty-select {
    border:1.5px solid #e2e8f0 !important; border-radius:10px !important; padding:7px 30px 7px 12px;
    font-size:13px; font-weight:600; color:#374151 !important; background:#fff; cursor:pointer;
    min-width:130px; appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat; background-position:right 9px center;
}
.whp-ty-select:focus { outline:none; border-color:#e11d48 !important; box-shadow:0 0 0 3px rgba(225,29,72,.12); }

/* ===== RICH PREVIEW PANEL ===== */
.whp-rp-panel { background:#fff; border-radius:14px; box-shadow:0 1px 4px rgba(0,0,0,.07),0 0 0 1px #e8edf3; overflow:hidden; }
.whp-rp-head { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid #f1f5f9; }
.whp-rp-head > span { font-size:13px; font-weight:700; color:#0f172a; }
.whp-rp-body { padding:18px; background:#f1f5f9; max-height:700px; overflow-y:auto; }
/* Constrain real layout inside preview panel */
.whp-rp-body .whp-thankyou-wrap { max-width:440px; margin:0 auto; transition:max-width .2s; }
/* Ẩn print invoice trong preview */
.whp-rp-body .whp-ty__print-invoice { display:none !important; }

/* ===== COLOR ROW (3 dòng màu mới) ===== */
.whp-ty-colorrow { margin-bottom:14px; }
.whp-ty-colorrow:last-child { margin-bottom:0; }
.whp-ty-colorrow-label { font-size:12.5px; font-weight:700; color:#374151; margin-bottom:8px; }
/* Fixed layout: swatches in flex-1, then hex input fixed width, then preview */
.whp-ty-colorrow-body  { display:flex; align-items:center; gap:0; flex-wrap:nowrap; }
.whp-ty-swatches-group { display:flex; align-items:center; gap:6px; flex:1; flex-wrap:wrap; }
.whp-ty-swatch-circle {
    display:inline-block;
    width:28px; height:28px; border-radius:50%; cursor:pointer; flex-shrink:0;
    border:3px solid #e2e8f0; box-sizing:border-box; transition:all .15s;
    outline:none; position:relative; padding:0;
}
.whp-ty-swatch-circle.active {
    border-color:var(--sw-c,#3b82f6) !important;
    box-shadow:0 0 0 2px #fff,0 0 0 4px var(--sw-c,#3b82f6);
}
.whp-ty-swatch-hex {
    width:110px; flex-shrink:0; margin-left:10px; min-height:0;
    border:1.5px solid #e2e8f0 !important; border-radius:12px !important;
    padding:6px 10px; font-size:12px; color:#374151 !important; background:#fff;
    font-family:monospace; transition:border-color .15s; outline:none;
    box-sizing:border-box; box-shadow:none !important; -webkit-appearance:none; appearance:none;
}
.whp-ty-swatch-hex:focus { border-color:#e11d48; box-shadow:0 0 0 3px rgba(225,29,72,.1); }
.whp-ty-swatch-preview {
    width:28px; height:28px; border-radius:6px; flex-shrink:0; margin-left:8px;
    border:1.5px solid #e2e8f0; box-sizing:border-box;
    transition:background .15s;
}

/* ===== IMPROVED SETROW (custom dropdown) ===== */
.whp-ty-setrow {
    display:flex; align-items:center; gap:12px;
    padding:11px 14px; border:1.5px solid #e8edf3; border-radius:10px;
    background:#fff; margin-top:10px; cursor:pointer;
    transition:border-color .15s, box-shadow .15s;
    position:relative; user-select:none;
}
.whp-ty-setrow:hover { border-color:#fecdd3; box-shadow:0 2px 8px rgba(225,29,72,.07); }
.whp-ty-setrow.open { border-color:#e11d48; box-shadow:0 2px 8px rgba(225,29,72,.12); }
.whp-ty-setrow-icon {
    width:34px; height:34px; border-radius:9px;
    background:#f8fafc; border:1px solid #e8edf3;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.whp-ty-setrow-label { flex:1; font-size:13px; font-weight:600; color:#0f172a; }
.whp-ty-setrow-right { display:flex; align-items:center; gap:6px; }
.whp-ty-setrow-val { font-size:12.5px; color:#64748b; font-weight:500; }
.whp-ty-setrow-chevron { color:#94a3b8; flex-shrink:0; transition:transform .2s; }
.whp-ty-setrow.open .whp-ty-setrow-chevron { transform:rotate(90deg); }
/* Custom dropdown panel */
.whp-ty-setrow-panel {
    display:none; position:absolute; top:calc(100% + 6px); right:0; z-index:999;
    min-width:200px; background:#fff; border-radius:10px;
    box-shadow:0 8px 24px rgba(15,23,42,.12),0 0 0 1px #e8edf3;
    overflow:hidden; padding:4px 0;
}
.whp-ty-setrow.open .whp-ty-setrow-panel { display:block; }
.whp-ty-setrow-opt {
    display:flex; align-items:center; gap:8px;
    padding:9px 14px; font-size:13px; color:#374151; cursor:pointer;
    transition:background .1s;
}
.whp-ty-setrow-opt:hover { background:#fff1f2; color:#e11d48; }
.whp-ty-setrow-opt.selected { background:#fff1f2; color:#e11d48; font-weight:600; }
.whp-ty-setrow-opt.selected::after {
    content:''; display:block; width:6px; height:6px; border-radius:50%;
    background:#e11d48; margin-left:auto; flex-shrink:0;
}

/* ===== DASHBOARD WRAPPER ===== */
.whp-ty-dash {
    font-family: inherit;
    max-width: 1200px;
    margin: 0 auto 60px;
    padding: 0 6px;
    box-sizing: border-box;
}

/* ===== CUSTOM HEADER ===== */
.whp-ty-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 0 16px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 0;
    flex-wrap: wrap; gap: 12px;
}
.whp-ty-head-left { display:flex; align-items:center; gap:12px; }
.whp-ty-head-title { margin:0; font-size:19px; font-weight:800; color:#0f172a; line-height:1.2; }
.whp-ty-head-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600;
}
.whp-ty-head-badge.on  { background:#ffe4e6; color:#e11d48; }
.whp-ty-head-badge.off { background:#f1f5f9; color:#64748b; }
.whp-ty-head-badge.on::before  { content:''; display:inline-block; width:6px; height:6px; border-radius:50%; background:#e11d48; }
.whp-ty-head-badge.off::before { content:''; display:inline-block; width:6px; height:6px; border-radius:50%; background:#94a3b8; }
.whp-ty-head-actions { display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.whp-ty-btn-ghost {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; border-radius:8px; border:1px solid #e2e8f0;
    background:#fff; color:#374151; font-size:13px; font-weight:500;
    text-decoration:none; cursor:pointer; transition:border-color .15s, background .15s;
}
.whp-ty-btn-ghost:hover { border-color:#e11d48; color:#be123c; background:#fff1f2; }
.whp-ty-btn-save {
    display:inline-flex; align-items:center; gap:7px;
    padding:11px 26px; border-radius:10px; border:none;
    background:#3858e9;
    color:#fff; font-size:13.5px; font-weight:600; cursor:pointer;
    box-shadow:0 4px 14px rgba(56,88,233,.28); transition:all .2s;
}
.whp-ty-btn-save:hover { background:#2563eb; transform:translateY(-1px); box-shadow:0 6px 18px rgba(56,88,233,.34); }

/* ===== TAB NAV ===== */
.whp-ty-tabs {
    display:flex; gap:2px; padding:4px;
    background:#f8fafc; border-radius:10px; border:1px solid #e2e8f0;
    margin:16px 0 20px; width:100%;
}
.whp-ty-tab {
    flex:1; padding:7px 12px; border-radius:7px; border:none; background:transparent;
    font-size:13px; font-weight:500; color:#64748b; cursor:pointer;
    transition:all .15s; white-space:nowrap; text-align:center;
    display:inline-flex; align-items:center; justify-content:center; gap:6px;
}
.whp-ty-tab.active { background:#fff; color:#e11d48; font-weight:700; box-shadow:0 1px 4px rgba(0,0,0,.08); }
.whp-ty-tab:hover:not(.active) { color:#334155; background:rgba(255,255,255,.6); }

/* ===== CARDS ===== */
.whp-ty-card {
    background:#fff; border-radius:12px;
    box-shadow:0 1px 4px rgba(0,0,0,.07), 0 0 0 1px #e8edf3;
    margin-bottom:16px; overflow:hidden;
}
/* Card chứa custom dropdown cần overflow visible để panel không bị cắt */
.whp-ty-card.has-dropdowns { overflow:visible; }
.whp-ty-card.has-dropdowns .whp-ty-card-inner { overflow:visible; }
.whp-ty-card-inner { padding:20px 22px; }
.whp-ty-card-head {
    display:flex; align-items:flex-start; gap:12px;
    padding-bottom:14px; margin-bottom:14px;
    border-bottom:1px solid #f1f5f9;
}
.whp-ty-card-icon {
    width:36px; height:36px; border-radius:9px;
    background:#fff1f2; display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.whp-ty-card-head h3 { margin:0 0 3px; font-size:15px; font-weight:700; color:#0f172a; }
.whp-ty-card-head p  { margin:0; font-size:12.5px; color:#64748b; }

/* ===== FIELD ROW ===== */
.whp-ty-field {
    display:flex; align-items:center; justify-content:space-between; gap:12px;
    padding:11px 0; border-bottom:1px solid #f1f5f9;
}
.whp-ty-field:last-child  { border-bottom:none; padding-bottom:0; }
.whp-ty-field:first-child { padding-top:0; }
.whp-ty-field-label { font-weight:600; font-size:13.5px; color:#0f172a; }
.whp-ty-field-desc  { font-size:12px; color:#64748b; margin-top:2px; }

/* ===== TOGGLE ===== */
.whp-ty-switch { position:relative; display:inline-block; width:46px; height:25px; flex-shrink:0; }
.whp-ty-switch input { opacity:0; width:0; height:0; }
.whp-ty-slider {
    position:absolute; inset:0;
    background:#cbd5e1; border-radius:25px;
    cursor:pointer; transition:background .22s;
}
.whp-ty-slider::after {
    content:''; position:absolute;
    width:17px; height:17px; background:#fff; border-radius:50%;
    left:4px; top:4px; transition:transform .22s;
    box-shadow:0 1px 4px rgba(0,0,0,.18);
}
.whp-ty-switch input:checked + .whp-ty-slider { background:#22c55e; }
.whp-ty-switch input:checked + .whp-ty-slider::after { transform:translateX(21px); }

/* ===== HEADER TOGGLE ===== */
.whp-ty-header-toggle { display:inline-flex; align-items:center; gap:10px; padding-left:56px; margin-top:6px; }
.whp-ty-htxt { font-size:13px; font-weight:700; color:#64748b; transition:color .2s; }
.whp-ty-htxt-on { color:#16a34a; }

/* ===== MAIN LAYOUT: 2-COL ===== */
.whp-ty-main { display:grid; grid-template-columns:1fr 440px; gap:20px; align-items:start; }
.whp-ty-main.is-full { grid-template-columns:1fr; } /* tab AI: ẩn preview, nội dung full 1200px */
@media (max-width:1080px) { .whp-ty-main { grid-template-columns:1fr; } .whp-ty-main-right { display:none; } }
.whp-ty-main-right { position:sticky; top:24px; }

/* ===== OVERVIEW (full-width settings) ===== */
.whp-ty-overview { display:block; }
.whp-ty-mockup { max-width:440px; margin:0 auto; }

/* ===== OVERVIEW REDESIGN ===== */
.whp-ov-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
@media (max-width:600px) { .whp-ov-stats { grid-template-columns:1fr; } }
.whp-ov-stat { background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.07),0 0 0 1px #e8edf3; padding:14px 16px; display:flex; align-items:center; gap:12px; }
.whp-ov-stat-ic { width:40px; height:40px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.whp-ov-stat-ic.rose   { background:#fff1f2; color:#e11d48; }
.whp-ov-stat-ic.purple { background:#f5f3ff; color:#9333ea; }
.whp-ov-stat-ic.blue   { background:#eff6ff; color:#3b82f6; }
.whp-ov-stat-lbl { font-size:11px; color:#64748b; margin-bottom:4px; }
.whp-ov-stat-val { font-size:20px; font-weight:800; color:#0f172a; line-height:1; }
.whp-ov-stat-sub { font-size:11px; color:#94a3b8; margin-left:4px; font-weight:400; }
.whp-ov-demo { font-size:11.5px; color:#e11d48; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:3px; margin-top:4px; }
.whp-ov-demo:hover { color:#be123c; }
.whp-ov-modules { display:grid; grid-template-columns:1fr 1fr; }
@media (max-width:600px) { .whp-ov-modules { grid-template-columns:1fr; } }
.whp-ov-module { display:flex; align-items:center; gap:12px; padding:13px 18px; border-bottom:1px solid #f1f5f9; transition:background .1s; }
.whp-ov-module:hover { background:#fafbfd; }
.whp-ov-module:nth-child(odd) { border-right:1px solid #f1f5f9; }
.whp-ov-module:nth-last-child(1),.whp-ov-module:nth-last-child(2) { border-bottom:none; }
.whp-ov-stat-ic svg { display:block !important; fill:none !important; stroke:currentColor !important; overflow:visible; }
.whp-ov-stat-ic svg path,.whp-ov-stat-ic svg rect,.whp-ov-stat-ic svg circle,.whp-ov-stat-ic svg line,.whp-ov-stat-ic svg polyline { fill:none !important; stroke:currentColor !important; }
.whp-ov-mod-icon { width:34px; height:34px; border-radius:9px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
.whp-ov-mod-icon .dashicons { font-size:16px !important; width:16px !important; height:16px !important; line-height:1 !important; }
.whp-ov-mod-icon.rose   { background:#fff1f2; color:#e11d48; }
.whp-ov-mod-icon.green  { background:#dcfce7; color:#16a34a; }
.whp-ov-mod-icon.grey   { background:#f1f5f9; color:#94a3b8; }
.whp-ov-mod-icon.blue   { background:#eff6ff; color:#3b82f6; }
.whp-ov-mod-icon.orange { background:#fff7ed; color:#ea580c; }
.whp-ov-mod-name   { font-size:13px; font-weight:600; color:#0f172a; }
.whp-ov-mod-status { font-size:11.5px; color:#94a3b8; margin-top:1px; }
.whp-ov-mod-right  { margin-left:auto; flex-shrink:0; }
.whp-ov-arrow { color:#cbd5e1; display:flex; align-items:center; }
.whp-ov-ai-pills { display:flex; flex-wrap:wrap; gap:5px; margin-top:10px; }
.whp-ov-ai-pill { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:20px; background:rgba(255,255,255,.18); font-size:11px; font-weight:600; color:#fff; border:1px solid rgba(255,255,255,.25); }
.whp-ov-ai-pill svg { width:10px !important; height:10px !important; display:block; flex-shrink:0; }

/* ===== DESIGN TAB (full-width settings) ===== */
.whp-ty-design-grid { display:block; }

/* ===== MODULE GRID ===== */
.whp-ty-modules { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:4px; }
@media (max-width:600px) { .whp-ty-modules { grid-template-columns:1fr; } }
.whp-ty-module {
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 14px; border-radius:10px;
    border:1.5px solid #e8edf3; background:#fafbfd;
    transition:border-color .15s;
}
.whp-ty-module:hover { border-color:#fecdd3; }
.whp-ty-module-left { display:flex; align-items:center; gap:11px; }
.whp-ty-module-dot {
    width:8px; height:8px; border-radius:50%; flex-shrink:0;
}
.whp-ty-module-dot.on  { background:#f43f5e; }
.whp-ty-module-dot.off { background:#cbd5e1; }
/* Professional icon box per module */
.whp-ty-module-icon {
    width:36px; height:36px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
    transition:background .18s, color .18s;
}
.whp-ty-module-icon.on  { background:#dcfce7; color:#16a34a; }
.whp-ty-module-icon.off { background:#f1f5f9; color:#94a3b8; }
.whp-ty-module-icon svg { width:18px !important; height:18px !important; display:block !important; vertical-align:middle; flex-shrink:0; }
.whp-ty-module-name { font-size:13px; font-weight:600; color:#1e293b; }
.whp-ty-module-sub  { font-size:11.5px; color:#94a3b8; margin-top:1px; }

/* ===== STATS ===== */
.whp-ty-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:16px; }
@media (max-width:600px) { .whp-ty-stats { grid-template-columns:1fr 1fr; } }
.whp-ty-stat {
    padding:14px 16px; border-radius:10px;
    background:#fff1f2; border:1px solid #fecdd3;
}
.whp-ty-stat-val { font-size:22px; font-weight:800; color:#be123c; line-height:1; }
.whp-ty-stat-lbl { font-size:11.5px; color:#64748b; margin-top:4px; }

/* ===== QUICK ACTIONS — 2×2 card grid ===== */
.whp-ty-qact-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:4px; }
.whp-ty-qact-card { display:flex; align-items:center; gap:14px; padding:14px 16px; border-radius:12px; border:1.5px solid #e8edf3; background:#fff; cursor:pointer; text-align:left; width:100%; transition:border-color .15s, background .15s, box-shadow .15s; }
.whp-ty-qact-card:hover { border-color:#e11d48; background:#fff8f9; box-shadow:0 2px 8px rgba(225,29,72,.07); }
.whp-ty-qact-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-ty-qact-body { flex:1; min-width:0; }
.whp-ty-qact-name { font-size:13px; font-weight:700; color:#0f172a; margin-bottom:3px; }
.whp-ty-qact-desc { font-size:11.5px; color:#94a3b8; line-height:1.45; }
.whp-ty-qact-arrow { color:#cbd5e1; flex-shrink:0; transition:color .15s; }
.whp-ty-qact-card:hover .whp-ty-qact-arrow { color:#e11d48; }

/* ===== PREVIEW DEVTABS ===== */
.whp-ty-preview-devtabs { display:flex; gap:4px; }
.whp-ty-dev-tab {
    padding:4px 10px; border-radius:6px; font-size:11.5px; font-weight:500;
    border:1px solid #e2e8f0; background:#fff; color:#64748b; cursor:pointer;
}
.whp-ty-dev-tab.active { background:#e11d48; color:#fff; border-color:#e11d48; }
.whp-ty-preview-footer { padding:10px 16px; border-top:1px solid #f1f5f9; background:#fff; }
.whp-ty-preview-footer p { margin:0; font-size:11px; color:#94a3b8; text-align:center; }

/* ===== AI PROMO ===== */
.whp-ty-ai-promo {
    padding:16px 18px; border-radius:10px;
    background:linear-gradient(100deg,#fff1f2,#ffe4e6);
    border:1px solid #fecdd3; display:flex; align-items:center; gap:14px;
}
.whp-ty-ai-promo-icon {
    width:42px; height:42px; border-radius:11px; flex-shrink:0;
    background:linear-gradient(135deg,#e11d48,#be123c);
    display:flex; align-items:center; justify-content:center;
    box-shadow:0 4px 12px rgba(225,29,72,.28);
}
.whp-ty-ai-promo-icon svg { width:22px !important; height:22px !important; display:block !important; }
.whp-ty-ai-promo h4 { margin:0 0 3px; font-size:13.5px; font-weight:700; color:#be123c; }
.whp-ty-ai-promo p  { margin:0; font-size:12px; color:#64748b; line-height:1.5; }

/* ===== LAYOUT CARDS ===== */
.whp-layouts { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; }
@media (max-width:600px) { .whp-layouts { grid-template-columns:1fr; } }
.whp-layout-card {
    position:relative; padding:14px 16px; border-radius:10px; cursor:pointer;
    transition:border-color .15s, background .15s; text-align:center;
    border:2px solid #e2e8f0; background:#fff;
}
.whp-layout-card input[type="radio"] { position:absolute; opacity:0; width:0; height:0; }
.whp-layout-card.active { border-color:#e11d48; background:#fff1f2; }

/* ===== COLOR SWATCHES ===== */
.whp-color-swatch { display:inline-flex; align-items:center; cursor:pointer; position:relative; flex-shrink:0; line-height:0; }
.whp-color-swatch input[type="radio"] { position:absolute; opacity:0; width:0; height:0; }

/* ===== SUB BLOCK ===== */
.whp-sub-block {
    margin-top:12px; padding:14px 16px;
    background:#fff1f2; border-radius:10px; border:1px solid #fecdd3;
}

/* ===== INFO NOTE ===== */
.whp-ty-note {
    display:flex; align-items:flex-start; gap:8px;
    padding:10px 12px; border-radius:8px;
    background:#f8fafc; border:1px solid #e2e8f0;
    font-size:12.5px; color:#64748b; line-height:1.5; margin-top:12px;
}

/* ===== PAYMENT TAB — NUMBERED SECTION + ROW ICON ===== */
.whp-pay-num {
    width:26px; height:26px; border-radius:50%;
    background:#e11d48; color:#fff;
    font-size:13px; font-weight:700; line-height:1;
    display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.whp-ty-field-icon {
    width:34px; height:34px; border-radius:9px;
    display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.whp-ty-field-icon svg { width:16px !important; height:16px !important; display:block !important; flex-shrink:0; }
.whp-ty-field-icon.green { background:#f0fdf4; color:#22c55e; }
.whp-ty-field-icon.blue  { background:#eff6ff; color:#3b82f6; }
.whp-ty-field-icon.rose  { background:#fff1f2; color:#e11d48; }
/* Payment + Advanced tab: text block flex:1 → label/desc bám trái, toggle bám phải */
[data-content="payment"] .whp-ty-field > div { flex:1; }
[data-content="payment"] .whp-ty-field > .whp-cd-sel { flex:none; margin-left:auto; }
[data-content="advanced"] .whp-ty-field > div { flex:1; }

/* ===== CONTACT CHANNEL ROWS ===== */
.whp-ty-ch-section { margin-top:12px; border:1.5px solid #e8edf3; border-radius:10px; overflow:hidden; }
.whp-ty-ch-section-head { padding:10px 14px 8px; background:#f8fafc; border-bottom:1px solid #f1f5f9; }
.whp-ty-ch-section-title { font-size:12.5px; font-weight:700; color:#374151; margin:0 0 2px; }
.whp-ty-ch-section-desc { font-size:11.5px; color:#94a3b8; margin:0; }
.whp-ty-ch-row { display:flex; align-items:center; gap:10px; padding:10px 14px; border-bottom:1px solid #f1f5f9; }
.whp-ty-ch-row:last-child { border-bottom:none; }
.whp-ty-ch-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-ty-ch-name { font-size:13.5px; font-weight:700; color:#0f172a; flex:0 0 90px; }
.whp-ty-ch-input-wrap { display:flex; align-items:center; border:1.5px solid #e2e8f0; border-radius:8px; overflow:hidden; flex:1; background:#fff; }
.whp-ty-ch-input-lbl { padding:6px 10px; font-size:12px; color:#94a3b8; white-space:nowrap; border-right:1.5px solid #e2e8f0; background:#f8fafc; flex-shrink:0; min-width:130px; }
.whp-ty-ch-input-val { padding:6px 10px; flex:1; border:none !important; outline:none; font-size:13px; color:#374151; min-width:0; }
.whp-ty-ch-input-val:focus { box-shadow:inset 0 0 0 2px #e11d48; }

/* ===== COUNTDOWN CUSTOM SELECT (chấm màu per option) ===== */
.whp-cd-sel { position:relative; flex:none; }
.whp-cd-sel-btn {
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 12px 7px 10px;
    border:1.5px solid #e2e8f0 !important; border-radius:10px !important;
    background:#fff; cursor:pointer; font-size:13px; font-weight:600; color:#374151 !important;
    min-width:120px; transition:border-color .15s, box-shadow .15s;
}
.whp-cd-sel-btn:hover { border-color:#e11d48 !important; }
.whp-cd-sel.open .whp-cd-sel-btn { border-color:#e11d48 !important; box-shadow:0 0 0 3px rgba(225,29,72,.12); }
.whp-cd-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; display:inline-block; }
.whp-cd-chev { color:#94a3b8; margin-left:auto; flex-shrink:0; transition:transform .2s; }
.whp-cd-sel.open .whp-cd-chev { transform:rotate(180deg); }
.whp-cd-panel {
    display:none; position:absolute; top:calc(100% + 5px); right:0; z-index:9999;
    min-width:150px; background:#fff; border-radius:10px;
    box-shadow:0 8px 24px rgba(15,23,42,.12),0 0 0 1px #e8edf3; padding:4px 0;
}
.whp-cd-sel.open .whp-cd-panel { display:block; }
.whp-cd-opt {
    display:flex; align-items:center; gap:10px;
    padding:9px 14px; font-size:13px; color:#374151; cursor:pointer; transition:background .1s;
}
.whp-cd-opt:hover { background:#fff1f2; }
.whp-cd-opt.selected { background:#fff1f2; color:#e11d48; font-weight:600; }

/* ===== SAVE BAR ===== */
.whp-ty-save-bar {
    background:#fff; border-radius:12px;
    box-shadow:0 1px 4px rgba(0,0,0,.07), 0 0 0 1px #e8edf3;
    padding:14px 22px; display:flex; align-items:center; justify-content:space-between;
    margin-top:8px;
}
.whp-ty-save-note { font-size:12.5px; color:#64748b; }
.whp-ty-save-note strong { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:2px; }

/* ===== AI TAB DASHBOARD ===== */
.whp-ai-dash { display:grid; grid-template-columns:1fr 340px; gap:16px; align-items:start; }
@media (max-width:900px) { .whp-ai-dash { grid-template-columns:1fr; } }
/* ===== Guide tab — Flat list style ===== */
.whp-guide-sec-card { border-top-width:3px !important; border-top-style:solid !important; }
.whp-guide-sec-hd { display:flex; align-items:center; gap:12px; margin-bottom:16px; padding-bottom:14px; border-bottom:1px solid #f1f5f9; }
.whp-guide-sec-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-guide-sec-title { font-size:14px; font-weight:700; color:#0f172a; line-height:1.3; }
.whp-guide-sec-sub { font-size:11.5px; color:#94a3b8; margin-top:2px; }
.whp-guide-list-item { display:flex; align-items:flex-start; gap:10px; padding:11px 0; border-bottom:1px solid #f8fafc; }
.whp-guide-list-item:last-child { border-bottom:none; padding-bottom:0; }
.whp-guide-list-item:first-child { padding-top:0; }
.whp-guide-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.whp-guide-list-title { font-size:13px; font-weight:700; color:#0f172a; margin-bottom:3px; line-height:1.35; }
.whp-guide-list-desc  { font-size:12px; color:#64748b; line-height:1.6; }
/* guide page header banner */
.whp-guide-banner { border-radius:14px; background:linear-gradient(110deg,#f8fafc 0%,#f1f5f9 100%); border:1px solid #e2e8f0; padding:20px 24px; margin-bottom:20px; display:flex; align-items:center; gap:16px; flex-wrap:wrap; }
.whp-guide-banner-icon { width:44px; height:44px; border-radius:12px; background:linear-gradient(135deg,#334155,#0f172a); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 3px 10px rgba(15,23,42,.2); }
.whp-guide-banner-title { font-size:16px; font-weight:700; color:#0f172a; margin-bottom:4px; }
.whp-guide-banner-sub { font-size:12.5px; color:#64748b; line-height:1.5; }
/* legacy — keep for other uses */
.whp-guide-field-lbl { font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px; }
.whp-guide-inp { border:1px solid #e2e8f0; border-radius:8px; padding:7px 10px; font-size:12.5px; color:#0f172a; width:100%; box-sizing:border-box; outline:none; background:#fff; font-family:inherit; resize:none; }
.whp-guide-inp:focus { border-color:#e11d48; box-shadow:0 0 0 3px rgba(225,29,72,.07); }
.whp-guide-step-badge { width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg,#e11d48,#be123c); color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-guide-widget-hd { display:flex; align-items:center; gap:10px; margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #f1f5f9; }
.whp-guide-widget-hd-icon { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#fb923c,#ea580c); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-gprev-sec-name { font-size:10.5px; font-weight:700; color:#be123c; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
.whp-ai-feat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-top:16px; }
@media (max-width:900px) { .whp-ai-feat-grid { grid-template-columns:1fr 1fr; } }
@media (max-width:600px) { .whp-ai-feat-grid { grid-template-columns:1fr; } }
.whp-ai-feat-card { padding:16px; border-radius:12px; background:#f8fafc; border:1px solid #e8edf3; border-left:3px solid #e2e8f0; transition:box-shadow .15s; }
.whp-ai-feat-card.is-on { background:#f0fdf4; border-color:#bbf7d0; border-left-color:#16a34a; }
.whp-ai-feat-card:not(.is-on) { opacity:.82; }
.whp-ai-feat-icon { width:40px; height:40px; border-radius:10px; background:#e2e8f0; display:flex; align-items:center; justify-content:center; margin-bottom:10px; }
.whp-ai-feat-card.is-on .whp-ai-feat-icon { background:#dcfce7; }
.whp-ai-feat-name { font-size:13px; font-weight:700; color:#0f172a; margin-bottom:4px; }
.whp-ai-feat-desc { font-size:11.5px; color:#64748b; line-height:1.5; margin-bottom:8px; }
.whp-ai-feat-badge { display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; padding:3px 8px; border-radius:20px; background:#f1f5f9; color:#94a3b8; }
.whp-ai-conn-chips { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; }
.whp-ai-conn-chip { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:600; border:1px solid #e2e8f0; background:#f8fafc; color:#64748b; }
.whp-ai-conn-chip.ok { background:#f0fdf4; border-color:#bbf7d0; color:#16a34a; }
.whp-ai-conn-chip.warn { background:#fff7ed; border-color:#fed7aa; color:#ea580c; }
.whp-ai-channel-row { display:flex; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9; gap:10px; cursor:pointer; }
.whp-ai-channel-row:last-child { border-bottom:none; }
.whp-ai-channel-icon { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-ai-channel-name { flex:1; font-size:13px; font-weight:600; color:#0f172a; }
.whp-ai-channel-status { font-size:11.5px; color:#16a34a; font-weight:600; margin-right:4px; }
.whp-ai-risk-row { display:flex; align-items:center; padding:9px 0; border-bottom:1px solid #f1f5f9; gap:10px; }
.whp-ai-risk-row:last-child { border-bottom:none; }
.whp-ai-risk-icon { width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-ai-risk-lbl { flex:1; font-size:12.5px; font-weight:600; color:#0f172a; }
.whp-ai-risk-desc { font-size:11px; color:#64748b; }
.whp-ai-gauge-wrap { text-align:center; padding:10px 0 4px; }
.whp-ai-gauge-num { font-size:26px; font-weight:800; color:#0f172a; line-height:1; }
.whp-ai-gauge-lbl { font-size:11px; color:#64748b; margin-top:3px; }
.whp-ai-gauge-safe { display:inline-block; margin-top:4px; font-size:11px; font-weight:700; color:#16a34a; background:#dcfce7; padding:2px 9px; border-radius:20px; }
.whp-ai-banner { display:flex; align-items:center; gap:14px; padding:16px 20px; border-radius:12px; background:linear-gradient(100deg,#fff1f2,#ffe4e6); border:1px solid #fecdd3; margin-top:16px; }
.whp-ai-banner-txt { flex:1; }
.whp-ai-banner-txt strong { display:block; font-size:14px; font-weight:700; color:#be123c; margin-bottom:3px; }
.whp-ai-banner-txt p { margin:0; font-size:12.5px; color:#64748b; }
.whp-ai-btn-rose { display:inline-flex; align-items:center; gap:6px; padding:8px 18px; border-radius:8px; border:none; background:#e11d48; color:#fff; font-size:13px; font-weight:600; cursor:pointer; text-decoration:none; white-space:nowrap; transition:background .15s; }
.whp-ai-btn-rose:hover { background:#be123c; color:#fff; }

/* ===== GRADIENT HEADER CARD (Rose) ===== */
.whp-ty-header-card {
    position:relative;
    background:linear-gradient(100deg,#ffffff 0%,#fff1f2 45%,#ffe4e6 100%);
    border-radius:20px;
    box-shadow:0 4px 24px rgba(225,29,72,.1), 0 0 0 1px #fecdd3;
    margin-bottom:20px; overflow:hidden;
    min-height:148px; display:flex; align-items:stretch;
}
.whp-ty-header-left {
    position:relative; z-index:2;
    padding:26px 36px;
    display:flex; flex-direction:column; justify-content:center; gap:8px;
    max-width:600px; flex-shrink:0;
}
.whp-ty-header-title-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.whp-ty-header-desc { color:#64748b; font-size:13px; line-height:1.55; margin:0; padding-left:56px; max-width:460px; }
.whp-ty-header-icon-box {
    width:44px; height:44px; border-radius:12px;
    background:linear-gradient(135deg,#e11d48,#be123c);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; box-shadow:0 4px 12px rgba(225,29,72,.3);
}
.whp-ty-header-right {
    position:absolute; inset:0 0 0 42%;
    overflow:hidden; pointer-events:none;
}
/* ===== BOTTOM SAVE BAR (full width, standalone) ===== */
.whp-ty-bottom-save-bar {
    background:#fff; border-radius:14px;
    box-shadow:0 2px 12px rgba(0,0,0,.08), 0 0 0 1px #e8edf3;
    padding:16px 24px; display:flex; align-items:center; justify-content:space-between;
    margin-top:20px; gap:16px; width:100%; box-sizing:border-box;
}
.whp-ty-save-hint { display:inline-flex; align-items:center; gap:9px; }
.whp-ty-save-hint-ic {
    width:30px; height:30px; border-radius:50%; flex-shrink:0;
    background:#eff2fe; display:flex; align-items:center; justify-content:center;
}
.whp-ty-save-hint-txt strong { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:1px; }
.whp-ty-save-hint-txt span { font-size:12px; color:#94a3b8; }
@media (max-width:600px) {
    .whp-ty-header-card { min-height:auto; }
    .whp-ty-header-left { padding:20px; }
    .whp-ty-header-right { display:none; }
    .whp-ty-bottom-save-bar { flex-direction:column; align-items:stretch; }
    .whp-ty-btn-save { width:100%; justify-content:center; }
}
/* Disabled state when master toggle is OFF */
#whp-ty-content-wrap.ty-disabled { opacity:0.4; pointer-events:none; user-select:none; transition:opacity 0.3s; }
</style>
<style>
<?php
// Load CSS thật của thankyou page vào admin preview
$_ty_css_path = MB_WHP_PATH . 'assets/frontend/css/mb-hp-thankyou.css';
if ( file_exists( $_ty_css_path ) ) {
    // Scoped vào .whp-rp-body để tránh ảnh hưởng admin UI
    $css_content = file_get_contents( $_ty_css_path );
    echo $css_content;
}
?>
</style>

<?php if (isset($isSubmit) && $isSubmit == 1) : ?>
<div class="mb-wph-notify"><?php echo esc_html__('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

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
</style>
<div id="whp-toast-wrap"></div>

<div class="whp-ty-dash">

    <form method="POST" action="" id="whp-ty-form">
    <?php wp_nonce_field('_token', '_token'); ?>

    <!-- GRADIENT HEADER CARD -->
    <div class="whp-ty-header-card">
        <div class="whp-ty-header-left">
            <div class="whp-ty-header-title-row">
                <div class="whp-ty-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" fill="#fff" fill-opacity=".2"/>
                        <path d="M5 13l4 4L19 7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1 style="font-size:22px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.3px;"><?php esc_html_e('Trang đơn hàng thành công', 'whp'); ?></h1>
            </div>
            <p class="whp-ty-header-desc"><?php esc_html_e('Thay thế trang cảm ơn WooCommerce mặc định bằng giao diện tùy chỉnh chuyên nghiệp.', 'whp'); ?></p>
            <div class="whp-ty-header-toggle">
                <label class="whp-ty-switch">
                    <input type="checkbox" id="whp-main-enable" name="whp_woo_thankyou_enable" value="1" <?php checked($whp_woo_thankyou_enable, '1'); ?>>
                    <span class="whp-ty-slider"></span>
                </label>
                <span class="whp-ty-htxt<?php echo $ty_on ? ' whp-ty-htxt-on' : ''; ?>" id="whp-main-htxt">
                    <?php echo $ty_on ? esc_html__('Đang bật', 'whp') : esc_html__('Đang tắt', 'whp'); ?>
                </span>
            </div>
        </div>
        <div class="whp-ty-header-right">
            <svg viewBox="0 0 620 148" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="ty_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#fff1f2" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#ffe4e6" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#fecdd3" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="ty_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(225,29,72,0.18)"/>
                    </filter>
                    <filter id="ty_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(225,29,72,0.12)"/>
                    </filter>
                </defs>
                <rect width="620" height="148" fill="url(#ty_hbg)"/>
                <circle cx="540" cy="16" r="60" fill="#e11d48" fill-opacity=".04"/>
                <circle cx="600" cy="138" r="36" fill="#fb7185" fill-opacity=".05"/>
                <!-- Order success card -->
                <g filter="url(#ty_sh)">
                    <rect x="330" y="18" width="140" height="112" rx="10" fill="#fff"/>
                    <rect x="330" y="18" width="140" height="38" rx="10" fill="#e11d48"/>
                    <rect x="330" y="44" width="140" height="12" fill="#e11d48"/>
                    <circle cx="400" cy="36" r="12" fill="#fff" fill-opacity=".2"/>
                    <path d="M393 36l4 4 8-8" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <text x="400" y="56" font-size="9" font-weight="700" fill="#fff" text-anchor="middle" font-family="sans-serif">Đặt hàng thành công</text>
                    <rect x="344" y="68" width="112" height="7" rx="3" fill="#ffe4e6"/>
                    <rect x="344" y="80" width="80" height="7" rx="3" fill="#ffe4e6"/>
                    <rect x="344" y="96" width="50" height="20" rx="5" fill="#e11d48" fill-opacity=".15"/>
                    <rect x="402" y="96" width="50" height="20" rx="5" fill="#fecdd3"/>
                </g>
                <!-- Check badge -->
                <g filter="url(#ty_shSm)">
                    <circle cx="508" cy="74" r="34" fill="#fff"/>
                    <circle cx="508" cy="74" r="20" fill="#e11d48" fill-opacity=".1"/>
                    <path d="M498 74l5 5 10-10" stroke="#e11d48" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <!-- Floating star -->
                <circle cx="290" cy="38" r="5" fill="#e11d48" fill-opacity=".15"/>
                <circle cx="570" cy="50" r="3" fill="#fb7185" fill-opacity=".2"/>
                <circle cx="480" cy="120" r="4" fill="#e11d48" fill-opacity=".1"/>
            </svg>
        </div>
    </div>

    <!-- CONTENT WRAP (dims when master toggle is OFF) -->
    <div id="whp-ty-content-wrap">

    <!-- TABS NAV — full width, above the 2-col grid -->
    <div class="whp-ty-tabs">
        <button type="button" class="whp-ty-tab active" data-tab="overview">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
            <?php esc_html_e('Tổng quan', 'whp'); ?>
        </button>
        <button type="button" class="whp-ty-tab" data-tab="design">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
            <?php esc_html_e('Giao diện', 'whp'); ?>
        </button>
        <button type="button" class="whp-ty-tab" data-tab="payment">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
            <?php esc_html_e('Thanh toán', 'whp'); ?>
        </button>
        <button type="button" class="whp-ty-tab" data-tab="advanced">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <?php esc_html_e('Nâng cao', 'whp'); ?>
        </button>
        <button type="button" class="whp-ty-tab" data-tab="ai">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/><path d="M5 14l.8 2.2L8 17l-2.2.8L5 20l-.8-2.2L2 17l2.2-.8L5 14z"/></svg>
            <?php esc_html_e('AI tích hợp', 'whp'); ?>
        </button>
        <button type="button" class="whp-ty-tab" data-tab="guide">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <?php esc_html_e('Hướng dẫn', 'whp'); ?>
        </button>
    </div>

    <!-- MAIN 2-COL LAYOUT -->
    <div class="whp-ty-main">
    <div class="whp-ty-main-left">

    <!-- ============================================================ -->
    <!-- TAB: TỔNG QUAN                                               -->
    <!-- ============================================================ -->
    <div class="whp-ty-tab-content" data-content="overview">
    <div class="whp-ty-overview">

        <?php
        $active_count = (int)$whp_woo_thankyou_show_timeline + (int)$whp_woo_thankyou_show_qr_large
            + (int)$whp_woo_thankyou_countdown_enable + (int)$whp_woo_thankyou_trust_badges
            + (int)$whp_woo_thankyou_transfer_btn + (int)$whp_woo_thankyou_copy_account
            + (int)$whp_woo_thankyou_copy_content;
        $btn_count = (int)$whp_woo_thankyou_btn_continue + (int)$whp_woo_thankyou_btn_contact
            + (int)$whp_woo_thankyou_btn_view_order + (int)$whp_woo_thankyou_btn_invoice;
        ?>

        <!-- Stats 3 cards -->
        <!-- Module status grid -->
        <div class="whp-ty-card" style="margin-bottom:16px">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 20px 12px;border-bottom:1px solid #f1f5f9">
                <div style="font-size:14px;font-weight:700;color:#0f172a"><?php esc_html_e('Trạng thái module','whp'); ?></div>
                <button type="button" class="whp-ty-btn-ghost" style="padding:5px 12px;font-size:12px;gap:5px" onclick="whpTyGoTab('design')">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    <?php esc_html_e('Cấu hình nhanh','whp'); ?>
                </button>
            </div>
            <div class="whp-ov-modules">
                <!-- Giao diện -->
                <div class="whp-ov-module">
                    <span class="whp-ov-mod-icon rose"><i class="dashicons dashicons-admin-appearance"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('Giao diện','whp'); ?></div>
                        <div class="whp-ov-mod-status"><?php echo esc_html($layout_names[$whp_woo_thankyou_layout] ?? 'Classic'); ?></div>
                    </div>
                    <button type="button" class="whp-ty-btn-ghost whp-ov-mod-right" style="padding:4px 10px;font-size:11.5px" onclick="whpTyGoTab('design')"><?php esc_html_e('Đổi','whp'); ?></button>
                </div>
                <!-- Timeline -->
                <div class="whp-ov-module">
                    <span class="whp-ov-mod-icon <?php echo $whp_woo_thankyou_show_timeline ? 'green' : 'grey'; ?>"><i class="dashicons dashicons-list-view"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('Timeline đơn hàng','whp'); ?></div>
                        <div class="whp-ov-mod-status"><?php echo $whp_woo_thankyou_show_timeline ? esc_html__('Đang bật','whp') : esc_html__('Đang tắt','whp'); ?></div>
                    </div>
                    <span class="whp-ty-toggle-wrap whp-ov-mod-right"><span class="whp-ty-toggle-txt<?php echo $whp_woo_thankyou_show_timeline ? ' on' : ''; ?>"><?php echo $whp_woo_thankyou_show_timeline ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?></span><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_show_timeline" value="1" <?php checked($whp_woo_thankyou_show_timeline,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                </div>
                <!-- QR -->
                <div class="whp-ov-module">
                    <span class="whp-ov-mod-icon <?php echo $whp_woo_thankyou_show_qr_large ? 'green' : 'grey'; ?>"><i class="dashicons dashicons-camera"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('QR Thanh toán','whp'); ?></div>
                        <div class="whp-ov-mod-status"><?php echo $whp_woo_thankyou_show_qr_large ? esc_html__('QR lớn','whp') : esc_html__('Đang tắt','whp'); ?></div>
                    </div>
                    <span class="whp-ty-toggle-wrap whp-ov-mod-right"><span class="whp-ty-toggle-txt<?php echo $whp_woo_thankyou_show_qr_large ? ' on' : ''; ?>"><?php echo $whp_woo_thankyou_show_qr_large ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?></span><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_show_qr_large" value="1" <?php checked($whp_woo_thankyou_show_qr_large,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                </div>
                <!-- Countdown -->
                <div class="whp-ov-module">
                    <span class="whp-ov-mod-icon <?php echo $whp_woo_thankyou_countdown_enable ? 'green' : 'grey'; ?>"><i class="dashicons dashicons-clock"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('Countdown thanh toán','whp'); ?></div>
                        <div class="whp-ov-mod-status"><?php echo $whp_woo_thankyou_countdown_enable ? esc_html($whp_woo_thankyou_countdown_minutes . ' phút') : esc_html__('Đang tắt','whp'); ?></div>
                    </div>
                    <span class="whp-ty-toggle-wrap whp-ov-mod-right"><span class="whp-ty-toggle-txt<?php echo $whp_woo_thankyou_countdown_enable ? ' on' : ''; ?>"><?php echo $whp_woo_thankyou_countdown_enable ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?></span><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_countdown_enable" value="1" <?php checked($whp_woo_thankyou_countdown_enable,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                </div>
                <!-- Trust Badge -->
                <div class="whp-ov-module">
                    <span class="whp-ov-mod-icon <?php echo $whp_woo_thankyou_trust_badges ? 'green' : 'grey'; ?>"><i class="dashicons dashicons-shield-alt"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('Trust Badge','whp'); ?></div>
                        <div class="whp-ov-mod-status"><?php echo $whp_woo_thankyou_trust_badges ? esc_html__('Đang bật','whp') : esc_html__('Đang tắt','whp'); ?></div>
                    </div>
                    <span class="whp-ty-toggle-wrap whp-ov-mod-right"><span class="whp-ty-toggle-txt<?php echo $whp_woo_thankyou_trust_badges ? ' on' : ''; ?>"><?php echo $whp_woo_thankyou_trust_badges ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?></span><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_trust_badges" value="1" <?php checked($whp_woo_thankyou_trust_badges,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                </div>
                <!-- Xác nhận chuyển khoản -->
                <div class="whp-ov-module">
                    <span class="whp-ov-mod-icon <?php echo $whp_woo_thankyou_transfer_btn ? 'green' : 'grey'; ?>"><i class="dashicons dashicons-money-alt"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('Xác nhận chuyển khoản','whp'); ?></div>
                        <div class="whp-ov-mod-status"><?php echo $whp_woo_thankyou_transfer_btn ? esc_html__('Đang bật','whp') : esc_html__('Đang tắt','whp'); ?></div>
                    </div>
                    <span class="whp-ty-toggle-wrap whp-ov-mod-right"><span class="whp-ty-toggle-txt<?php echo $whp_woo_thankyou_transfer_btn ? ' on' : ''; ?>"><?php echo $whp_woo_thankyou_transfer_btn ? esc_html__('Bật','whp') : esc_html__('Tắt','whp'); ?></span><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_transfer_btn" value="1" <?php checked($whp_woo_thankyou_transfer_btn,'1'); ?> id="whp-transfer-toggle-ov"><span class="whp-ty-slider"></span></label></span>
                </div>
                <!-- Nút hành động -->
                <div class="whp-ov-module" style="cursor:pointer" onclick="whpTyGoTab('advanced')">
                    <span class="whp-ov-mod-icon blue"><i class="dashicons dashicons-admin-links"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('Nút hành động','whp'); ?></div>
                        <div class="whp-ov-mod-status"><?php echo esc_html($btn_count); ?> <?php esc_html_e('nút đang bật','whp'); ?></div>
                    </div>
                    <span class="whp-ov-arrow whp-ov-mod-right"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
                </div>
                <!-- Thông báo & Email -->
                <div class="whp-ov-module" style="cursor:pointer" onclick="whpTyGoTab('payment')">
                    <span class="whp-ov-mod-icon <?php echo $whp_woo_thankyou_transfer_email ? 'orange' : 'grey'; ?>"><i class="dashicons dashicons-email-alt"></i></span>
                    <div style="flex:1;min-width:0">
                        <div class="whp-ov-mod-name"><?php esc_html_e('Thông báo & Email','whp'); ?></div>
                        <div class="whp-ov-mod-status" style="<?php echo $whp_woo_thankyou_transfer_email ? 'color:#16a34a' : ''; ?>"><?php echo $whp_woo_thankyou_transfer_email ? esc_html__('Đã kết nối','whp') : esc_html__('Chưa bật','whp'); ?></div>
                    </div>
                    <span class="whp-ov-arrow whp-ov-mod-right"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></span>
                </div>
            </div>
        </div>

        <!-- AI Banner -->
        <?php if (!whp_get_setting('whp_aipay_enable')) : ?>
        <div style="padding:18px 20px;border-radius:12px;background:linear-gradient(105deg,#be123c 0%,#e11d48 55%,#fb7185 100%);display:flex;align-items:flex-start;gap:14px;margin-bottom:16px">
            <div style="width:42px;height:42px;border-radius:11px;flex-shrink:0;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.3)">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/></svg>
            </div>
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span style="font-size:14px;font-weight:800;color:#fff"><?php esc_html_e('AI Thanh Toán Premium','whp'); ?></span>
                    <span style="background:rgba(255,255,255,.25);color:#fff;font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:20px;border:1px solid rgba(255,255,255,.4)">NEW</span>
                </div>
                <p style="margin:0 0 8px;font-size:12px;color:rgba(255,255,255,.85);line-height:1.5"><?php esc_html_e('Tự động xác minh chuyển khoản, phát hiện gian lận và thông báo đa kênh với AI tích hợp.','whp'); ?></p>
                <div class="whp-ov-ai-pills">
                    <?php foreach ([__('OCR Biên lai','whp'),__('Xác minh tức thì','whp'),__('Phát hiện gian lận','whp'),__('Đa kênh thông báo','whp'),__('AI Copilot','whp'),__('Đối soát tự động','whp')] as $_pill) : ?>
                    <span class="whp-ov-ai-pill"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?php echo esc_html($_pill); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment')); ?>" class="whp-ai-btn-rose" style="flex-shrink:0;background:rgba(255,255,255,.22);border:1.5px solid rgba(255,255,255,.5);color:#fff;white-space:nowrap">
                <?php esc_html_e('Khám phá','whp'); ?> →
            </a>
        </div>
        <?php else : ?>
        <!-- AI đang bật — hiện tips tối ưu -->
        <div class="whp-ty-card" style="margin-bottom:16px">
            <div class="whp-ty-card-inner">
                <div style="display:flex;align-items:center;gap:7px;font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?php esc_html_e('Mẹo tối ưu trang cảm ơn','whp'); ?>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9">
                        <span style="width:32px;height:32px;border-radius:8px;background:#ede9fe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                        </span>
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:3px"><?php esc_html_e('Cá nhân hóa giao diện','whp'); ?></div>
                            <div style="font-size:11.5px;color:#64748b;line-height:1.5"><?php esc_html_e('Đổi màu chủ đạo và banner khớp nhận diện thương hiệu shop.','whp'); ?></div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9">
                        <span style="width:32px;height:32px;border-radius:8px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:3px"><?php esc_html_e('Bật đếm ngược thanh toán','whp'); ?></div>
                            <div style="font-size:11.5px;color:#64748b;line-height:1.5"><?php esc_html_e('Giới hạn thời gian chuyển khoản tăng tỉ lệ hoàn tất đơn.','whp'); ?></div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9">
                        <span style="width:32px;height:32px;border-radius:8px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 9h6v6H9z"/><path d="M9 3v3M15 3v3M9 18v3M15 18v3M3 9h3M3 15h3M18 9h3M18 15h3"/></svg>
                        </span>
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:3px"><?php esc_html_e('Dùng QR code lớn','whp'); ?></div>
                            <div style="font-size:11.5px;color:#64748b;line-height:1.5"><?php esc_html_e('QR kích thước lớn giúp khách quét nhanh trên mobile, giảm sai sót.','whp'); ?></div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9">
                        <span style="width:32px;height:32px;border-radius:8px;background:#dbeafe;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:#0f172a;margin-bottom:3px"><?php esc_html_e('Bật Trust Badge','whp'); ?></div>
                            <div style="font-size:11.5px;color:#64748b;line-height:1.5"><?php esc_html_e('Badge bảo mật và chính sách hoàn tiền tăng độ tin cậy đơn hàng.','whp'); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick actions -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px"><?php esc_html_e('Thao tác nhanh','whp'); ?></div>
                <div class="whp-ty-qact-grid">

                    <button type="button" class="whp-ty-qact-card" onclick="whpTyGoTab('design')">
                        <span class="whp-ty-qact-icon" style="background:#ede9fe">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                        </span>
                        <div class="whp-ty-qact-body">
                            <div class="whp-ty-qact-name"><?php esc_html_e('Giao diện','whp'); ?></div>
                            <div class="whp-ty-qact-desc"><?php esc_html_e('Màu sắc, banner, logo, layout trang cảm ơn','whp'); ?></div>
                        </div>
                        <svg class="whp-ty-qact-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>

                    <button type="button" class="whp-ty-qact-card" onclick="whpTyGoTab('payment')">
                        <span class="whp-ty-qact-icon" style="background:#dcfce7">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        </span>
                        <div class="whp-ty-qact-body">
                            <div class="whp-ty-qact-name"><?php esc_html_e('Thanh toán','whp'); ?></div>
                            <div class="whp-ty-qact-desc"><?php esc_html_e('QR code, tài khoản ngân hàng, chuyển khoản','whp'); ?></div>
                        </div>
                        <svg class="whp-ty-qact-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>

                    <button type="button" class="whp-ty-qact-card" onclick="whpTyGoTab('advanced')">
                        <span class="whp-ty-qact-icon" style="background:#fff7ed">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15"/></svg>
                        </span>
                        <div class="whp-ty-qact-body">
                            <div class="whp-ty-qact-name"><?php esc_html_e('Nâng cao','whp'); ?></div>
                            <div class="whp-ty-qact-desc"><?php esc_html_e('Timeline, nút CTA, thông báo, hóa đơn VAT','whp'); ?></div>
                        </div>
                        <svg class="whp-ty-qact-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>

                    <button type="button" class="whp-ty-qact-card" onclick="whpTyGoTab('ai')">
                        <span class="whp-ty-qact-icon" style="background:#f1f5f9">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/></svg>
                        </span>
                        <div class="whp-ty-qact-body">
                            <div class="whp-ty-qact-name"><?php esc_html_e('AI tích hợp','whp'); ?></div>
                            <div class="whp-ty-qact-desc"><?php esc_html_e('OCR biên lai, xác minh tức thì, phát hiện gian lận','whp'); ?></div>
                        </div>
                        <svg class="whp-ty-qact-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>

                </div>
            </div>
        </div>

    </div><!-- .whp-ty-overview -->
    </div>


    <!-- ============================================================ -->
    <!-- TAB: GIAO DIỆN                                               -->
    <!-- ============================================================ -->
    <div class="whp-ty-tab-content" data-content="design" style="display:none">
    <div class="whp-ty-design-grid">
        <!-- settings -->

        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <div class="whp-ty-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="5" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="3" y="12" width="7" height="9" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/></svg>
                    </div>
                    <div><h3><?php esc_html_e('Chọn giao diện', 'whp'); ?></h3><p><?php esc_html_e('Chọn kiểu hiển thị cho trang đơn hàng thành công', 'whp'); ?></p></div>
                </div>
                <div class="whp-layouts">
                    <label class="whp-layout-card <?php echo $whp_woo_thankyou_layout === 'classic' ? 'active' : ''; ?>" data-layout-card onclick="whpSetLayout(this,'classic')">
                        <input type="radio" name="whp_woo_thankyou_layout" value="classic" <?php checked($whp_woo_thankyou_layout, 'classic'); ?>>
                        <div style="margin-bottom:8px;display:flex;justify-content:center">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1.5" fill="#64748b" stroke="none"/><circle cx="3" cy="12" r="1.5" fill="#64748b" stroke="none"/><circle cx="3" cy="18" r="1.5" fill="#64748b" stroke="none"/></svg>
                        </div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;margin-bottom:3px"><?php esc_html_e('Classic', 'whp'); ?></div>
                        <div style="font-size:11.5px;color:#64748b"><?php esc_html_e('Gọn gàng, tối giản', 'whp'); ?></div>
                    </label>
                    <label class="whp-layout-card <?php echo $whp_woo_thankyou_layout === 'modern' ? 'active' : ''; ?>" data-layout-card onclick="whpSetLayout(this,'modern')">
                        <input type="radio" name="whp_woo_thankyou_layout" value="modern" <?php checked($whp_woo_thankyou_layout, 'modern'); ?>>
                        <div style="margin-bottom:8px;display:flex;justify-content:center">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="8" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/><rect x="13" y="13" width="8" height="8" rx="2"/></svg>
                        </div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;margin-bottom:3px"><?php esc_html_e('Modern', 'whp'); ?></div>
                        <div style="font-size:11.5px;color:#64748b"><?php esc_html_e('Hiện đại, card-based', 'whp'); ?></div>
                    </label>
                    <label class="whp-layout-card <?php echo $whp_woo_thankyou_layout === 'premium' ? 'active' : ''; ?>" data-layout-card onclick="whpSetLayout(this,'premium')">
                        <input type="radio" name="whp_woo_thankyou_layout" value="premium" <?php checked($whp_woo_thankyou_layout, 'premium'); ?>>
                        <div style="margin-bottom:8px;display:flex;justify-content:center">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2z"/></svg>
                        </div>
                        <div style="font-size:13.5px;font-weight:700;color:#0f172a;margin-bottom:3px"><?php esc_html_e('Premium Card', 'whp'); ?></div>
                        <div style="font-size:11.5px;color:#64748b"><?php esc_html_e('Cao cấp, đầy đủ', 'whp'); ?></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <div class="whp-ty-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="10.5" r="2.5"/><circle cx="8.5" cy="7.5" r="2.5"/><circle cx="6.5" cy="12.5" r="2.5"/><path d="M12 20v-5"/><path d="M9.5 17h5"/></svg>
                    </div>
                    <div><h3><?php esc_html_e('Tùy chỉnh màu sắc', 'whp'); ?></h3><p><?php esc_html_e('Màu chủ đạo, màu phụ và màu nền trang cảm ơn', 'whp'); ?></p></div>
                </div>

                <!-- Dòng 1: Màu chủ đạo -->
                <div class="whp-ty-colorrow">
                    <div class="whp-ty-colorrow-label"><?php esc_html_e('Màu chủ đạo', 'whp'); ?></div>
                    <div class="whp-ty-colorrow-body">
                        <div class="whp-ty-swatches-group">
                        <?php
                        $color_swatches = ['#3b82f6' => __('Xanh dương','whp'), '#22c55e' => __('Xanh lá','whp'), '#f97316' => __('Cam','whp'), '#9333ea' => __('Tím','whp'), '#ef4444' => __('Đỏ','whp'), '#1f2937' => __('Tối','whp')];
                        $color_swatch_keys = ['#3b82f6' => 'blue', '#22c55e' => 'green', '#f97316' => 'orange', '#9333ea' => 'purple', '#ef4444' => 'red', '#1f2937' => 'dark'];
                        foreach ($color_swatches as $hex => $lbl) :
                            $key = $color_swatch_keys[$hex];
                            $is_active = ($whp_woo_thankyou_color === $key);
                        ?>
                        <label class="whp-color-swatch" title="<?php echo esc_attr($lbl); ?>">
                            <input type="radio" name="whp_woo_thankyou_color" value="<?php echo esc_attr($key); ?>" <?php checked($whp_woo_thankyou_color, $key); ?> onchange="whpColorChange(this)">
                            <span class="whp-ty-swatch-circle <?php echo $is_active ? 'active' : ''; ?>"
                                  data-swatch="<?php echo esc_attr($key); ?>"
                                  data-swatch-color="<?php echo esc_attr($hex); ?>"
                                  style="background:<?php echo esc_attr($hex); ?>;--sw-c:<?php echo esc_attr($hex); ?>"></span>
                        </label>
                        <?php endforeach; ?>
                        <label class="whp-color-swatch" title="<?php esc_attr_e('Tùy chỉnh','whp'); ?>">
                            <input type="radio" name="whp_woo_thankyou_color" value="custom" <?php checked($whp_woo_thankyou_color,'custom'); ?> onchange="whpColorChange(this)">
                            <span class="whp-ty-swatch-circle <?php echo $whp_woo_thankyou_color==='custom' ? 'active' : ''; ?>"
                                  data-swatch="custom"
                                  data-swatch-color="<?php echo esc_attr($whp_woo_thankyou_color_custom); ?>"
                                  style="background:<?php echo esc_attr($whp_woo_thankyou_color_custom); ?>;--sw-c:<?php echo esc_attr($whp_woo_thankyou_color_custom); ?>;border-style:dashed">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.8)" stroke-width="2.5" stroke-linecap="round" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </span>
                        </label>
                        </div>
                        <input type="text" id="whp-color-text-input" name="whp_woo_thankyou_color_custom"
                               value="<?php echo esc_attr($whp_woo_thankyou_color_custom); ?>"
                               placeholder="#3b82f6" maxlength="7"
                               class="whp-ty-swatch-hex" autocomplete="off">
                        <div class="whp-ty-swatch-preview" id="whp-color1-preview"
                             style="background:<?php echo esc_attr($accent); ?>"></div>
                    </div>
                    <div id="whp-color-custom-row" style="display:none"></div><!-- backward compat hidden anchor -->
                    <input type="color" id="whp-color-picker-input" value="<?php echo esc_attr($whp_woo_thankyou_color_custom); ?>" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none">
                </div>

                <!-- Dòng 2: Màu phụ -->
                <div class="whp-ty-colorrow">
                    <div class="whp-ty-colorrow-label"><?php esc_html_e('Màu phụ', 'whp'); ?></div>
                    <div class="whp-ty-colorrow-body">
                        <div class="whp-ty-swatches-group">
                        <?php $color2_swatches = ['#f3f0ff','#eff6ff','#f0fdf4','#fff7ed','#fdf2f8','#f1f5f9']; ?>
                        <?php foreach ($color2_swatches as $hex2) :
                            $is_active2 = ($whp_woo_thankyou_color2 === $hex2);
                        ?>
                        <button type="button" class="whp-ty-swatch-circle <?php echo $is_active2 ? 'active' : ''; ?>"
                                data-color2-swatch="<?php echo esc_attr($hex2); ?>"
                                style="background:<?php echo esc_attr($hex2); ?>;--sw-c:<?php echo esc_attr($hex2); ?>;border-color:<?php echo $is_active2 ? esc_attr($hex2) : '#e2e8f0'; ?>"
                                onclick="whpColor2Set('<?php echo esc_js($hex2); ?>')"></button>
                        <?php endforeach; ?>
                        <button type="button" class="whp-ty-swatch-circle" id="whp-color2-cust-btn"
                                title="<?php esc_attr_e('Tùy chỉnh','whp'); ?>"
                                style="background:<?php echo esc_attr($whp_woo_thankyou_color2 ?: '#f3f0ff'); ?>;--sw-c:#94a3b8;border-style:dashed;position:relative">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(100,116,139,.8)" stroke-width="2.5" stroke-linecap="round" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                        </div>
                        <input type="color" id="whp-color2-picker" value="<?php echo esc_attr($whp_woo_thankyou_color2 ?: '#f3f0ff'); ?>" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none">
                        <input type="text" id="whp-color2-text"
                               name="whp_woo_thankyou_color2"
                               value="<?php echo esc_attr($whp_woo_thankyou_color2); ?>"
                               placeholder="#f3f0ff" maxlength="7"
                               class="whp-ty-swatch-hex"
                               oninput="whpColor2Input(this)">
                        <div class="whp-ty-swatch-preview" id="whp-color2-preview"
                             style="background:<?php echo esc_attr($whp_woo_thankyou_color2); ?>"></div>
                    </div>
                </div>

                <!-- Dòng 3: Màu nền -->
                <div class="whp-ty-colorrow">
                    <div class="whp-ty-colorrow-label"><?php esc_html_e('Màu nền', 'whp'); ?></div>
                    <div class="whp-ty-colorrow-body">
                        <div class="whp-ty-swatches-group">
                        <?php $bg_swatches = ['#ffffff','#f8fafc','#f1f5f9','#fafafa','#f5f3ff','#fef2f2']; ?>
                        <?php foreach ($bg_swatches as $bghex) :
                            $is_activebg = ($whp_woo_thankyou_bg === $bghex);
                        ?>
                        <button type="button" class="whp-ty-swatch-circle <?php echo $is_activebg ? 'active' : ''; ?>"
                                data-bg-swatch="<?php echo esc_attr($bghex); ?>"
                                style="background:<?php echo esc_attr($bghex); ?>;--sw-c:#94a3b8;border-color:<?php echo $is_activebg ? '#94a3b8' : '#e2e8f0'; ?>"
                                onclick="whpBgSet('<?php echo esc_js($bghex); ?>')"></button>
                        <?php endforeach; ?>
                        <button type="button" class="whp-ty-swatch-circle" id="whp-bg-cust-btn"
                                title="<?php esc_attr_e('Tùy chỉnh','whp'); ?>"
                                style="background:<?php echo esc_attr($whp_woo_thankyou_bg ?: '#ffffff'); ?>;--sw-c:#94a3b8;border-style:dashed;position:relative">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(100,116,139,.8)" stroke-width="2.5" stroke-linecap="round" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                        </div>
                        <input type="color" id="whp-bg-picker" value="<?php echo esc_attr($whp_woo_thankyou_bg ?: '#ffffff'); ?>" style="width:0;height:0;opacity:0;position:absolute;pointer-events:none">
                        <input type="text" id="whp-bg-text"
                               name="whp_woo_thankyou_bg"
                               value="<?php echo esc_attr($whp_woo_thankyou_bg); ?>"
                               placeholder="#ffffff" maxlength="7"
                               class="whp-ty-swatch-hex"
                               oninput="whpBgInput(this)">
                        <div class="whp-ty-swatch-preview" id="whp-bg-preview"
                             style="background:<?php echo esc_attr($whp_woo_thankyou_bg); ?>"></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- 4 setting rows: font / radius / shadow / spacing -->
        <div class="whp-ty-card has-dropdowns">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <div class="whp-ty-card-icon">
                        <!-- Sliders icon -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/><circle cx="8" cy="6" r="2" fill="#fff"/><circle cx="16" cy="12" r="2" fill="#fff"/><circle cx="10" cy="18" r="2" fill="#fff"/></svg>
                    </div>
                    <div><h3><?php esc_html_e('Tùy chỉnh chi tiết', 'whp'); ?></h3><p><?php esc_html_e('Tinh chỉnh kiểu chữ, bo góc, bóng đổ và khoảng cách', 'whp'); ?></p></div>
                </div>

                <!-- Kiểu chữ -->
                <div class="whp-ty-setrow" id="setrow-font" data-setrow="font">
                    <div class="whp-ty-setrow-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                    </div>
                    <div class="whp-ty-setrow-label"><?php esc_html_e('Kiểu chữ', 'whp'); ?></div>
                    <div class="whp-ty-setrow-right">
                        <span class="whp-ty-setrow-val" id="setrow-font-val"><?php echo esc_html($ty_font_opts[$whp_woo_thankyou_font] ?? 'Inter'); ?></span>
                        <svg class="whp-ty-setrow-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                    <input type="hidden" name="whp_woo_thankyou_font" id="whp-set-font" value="<?php echo esc_attr($whp_woo_thankyou_font); ?>">
                    <div class="whp-ty-setrow-panel" id="panel-font">
                        <?php foreach ($ty_font_opts as $k => $label) : ?>
                        <div class="whp-ty-setrow-opt <?php echo ($whp_woo_thankyou_font === $k) ? 'selected' : ''; ?>"
                             data-val="<?php echo esc_attr($k); ?>"
                             data-setrow-opt="font"><?php echo esc_html($label); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Bo góc -->
                <div class="whp-ty-setrow" id="setrow-radius" data-setrow="radius">
                    <div class="whp-ty-setrow-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12V6a2 2 0 0 1 2-2h6"/><path d="M20 12v6a2 2 0 0 1-2 2h-6"/><path d="M4 12v6a2 2 0 0 0 2 2h6" stroke-opacity=".3"/><path d="M20 12V6a2 2 0 0 0-2-2h-6" stroke-opacity=".3"/></svg>
                    </div>
                    <div class="whp-ty-setrow-label"><?php esc_html_e('Bo góc', 'whp'); ?></div>
                    <div class="whp-ty-setrow-right">
                        <span class="whp-ty-setrow-val" id="setrow-radius-val"><?php echo esc_html($ty_radius_opts[$whp_woo_thankyou_radius] ?? __('Vừa (12px)','whp')); ?></span>
                        <svg class="whp-ty-setrow-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                    <input type="hidden" name="whp_woo_thankyou_radius" id="whp-set-radius" value="<?php echo esc_attr($whp_woo_thankyou_radius); ?>">
                    <div class="whp-ty-setrow-panel" id="panel-radius">
                        <?php foreach ($ty_radius_opts as $k => $label) : ?>
                        <div class="whp-ty-setrow-opt <?php echo ($whp_woo_thankyou_radius === $k) ? 'selected' : ''; ?>"
                             data-val="<?php echo esc_attr($k); ?>"
                             data-setrow-opt="radius"><?php echo esc_html($label); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Bóng đổ -->
                <div class="whp-ty-setrow" id="setrow-shadow" data-setrow="shadow">
                    <div class="whp-ty-setrow-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="12" height="12" rx="2"/><rect x="10" y="10" width="12" height="12" rx="2" fill="#fff1f2" stroke="#fecdd3"/></svg>
                    </div>
                    <div class="whp-ty-setrow-label"><?php esc_html_e('Bóng đổ', 'whp'); ?></div>
                    <div class="whp-ty-setrow-right">
                        <span class="whp-ty-setrow-val" id="setrow-shadow-val"><?php echo esc_html($ty_shadow_opts[$whp_woo_thankyou_shadow] ?? __('Trung bình','whp')); ?></span>
                        <svg class="whp-ty-setrow-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                    <input type="hidden" name="whp_woo_thankyou_shadow" id="whp-set-shadow" value="<?php echo esc_attr($whp_woo_thankyou_shadow); ?>">
                    <div class="whp-ty-setrow-panel" id="panel-shadow">
                        <?php foreach ($ty_shadow_opts as $k => $label) : ?>
                        <div class="whp-ty-setrow-opt <?php echo ($whp_woo_thankyou_shadow === $k) ? 'selected' : ''; ?>"
                             data-val="<?php echo esc_attr($k); ?>"
                             data-setrow-opt="shadow"><?php echo esc_html($label); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Khoảng cách -->
                <div class="whp-ty-setrow" id="setrow-spacing" data-setrow="spacing">
                    <div class="whp-ty-setrow-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"/><path d="M17 5l-5-3-5 3"/><path d="M17 19l-5 3-5-3"/><line x1="6" y1="12" x2="18" y2="12" stroke-opacity=".4"/></svg>
                    </div>
                    <div class="whp-ty-setrow-label"><?php esc_html_e('Khoảng cách', 'whp'); ?></div>
                    <div class="whp-ty-setrow-right">
                        <span class="whp-ty-setrow-val" id="setrow-spacing-val"><?php echo esc_html($ty_spacing_opts[$whp_woo_thankyou_spacing] ?? __('Rộng rãi','whp')); ?></span>
                        <svg class="whp-ty-setrow-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                    </div>
                    <input type="hidden" name="whp_woo_thankyou_spacing" id="whp-set-spacing" value="<?php echo esc_attr($whp_woo_thankyou_spacing); ?>">
                    <div class="whp-ty-setrow-panel" id="panel-spacing">
                        <?php foreach ($ty_spacing_opts as $k => $label) : ?>
                        <div class="whp-ty-setrow-opt <?php echo ($whp_woo_thankyou_spacing === $k) ? 'selected' : ''; ?>"
                             data-val="<?php echo esc_attr($k); ?>"
                             data-setrow-opt="spacing"><?php echo esc_html($label); ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- END design-grid -->
    </div><!-- end design tab -->

    <!-- ============================================================ -->
    <!-- TAB: THANH TOÁN                                              -->
    <!-- ============================================================ -->
    <div class="whp-ty-tab-content" data-content="payment" style="display:none">

        <!-- 1. QR Thanh toán nâng cao -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-pay-num">1</span>
                    <div>
                        <h3><?php esc_html_e('QR Thanh toán nâng cao','whp'); ?></h3>
                        <p><?php esc_html_e('Tùy chọn hiển thị và tương tác với mã QR chuyển khoản','whp'); ?></p>
                    </div>
                </div>
                <?php if (!$whp_ty_wallet_ready) : ?>
                <div class="whp-ty-qr-warn" style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:10px;background:#fff7ed;border:1px solid #fed7aa;margin-bottom:14px">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="9" x2="12" y2="13" stroke="#ea580c" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="17" x2="12.01" y2="17" stroke="#ea580c" stroke-width="2" stroke-linecap="round"/></svg>
                    <div style="flex:1">
                        <div style="font-size:13px;font-weight:700;color:#9a3412;margin-bottom:2px"><?php esc_html_e('Chưa bật cổng ví điện tử nào','whp'); ?></div>
                        <div style="font-size:12.5px;color:#c2410c;line-height:1.5"><?php esc_html_e('Mã QR chỉ hiển thị khi khách thanh toán qua một ví điện tử (MoMo, ZaloPay, VNPAY, ShopeePay) đã được bật và cấu hình ảnh QR. Hãy bật và cấu hình ví trước.','whp'); ?></div>
                        <a href="<?php echo esc_url($whp_ty_wallet_url); ?>" style="display:inline-flex;align-items:center;gap:5px;margin-top:8px;padding:6px 12px;border-radius:7px;background:#ea580c;color:#fff;font-size:12px;font-weight:600;text-decoration:none">
                            <?php esc_html_e('Cấu hình ví điện tử','whp'); ?> →
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Hiển thị QR lớn','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Phóng to mã QR để khách dễ quét hơn','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_show_qr_large" value="1" <?php checked($whp_woo_thankyou_show_qr_large,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Nút sao chép số tài khoản','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Thêm nút copy số tài khoản ngân hàng','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_copy_account" value="1" <?php checked($whp_woo_thankyou_copy_account,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Nút sao chép nội dung chuyển khoản','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Sao chép nhanh nội dung chuyển khoản (mã đơn hàng)','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_copy_content" value="1" <?php checked($whp_woo_thankyou_copy_content,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.74a16 16 0 0 0 6.29 6.29l.97-.97a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Nút liên hệ hỗ trợ thanh toán','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Hiển thị nút liên hệ nhanh khi khách gặp sự cố','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_show_support_btn" value="1" <?php checked($whp_woo_thankyou_show_support_btn,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
            </div>
        </div>

        <!-- 2. Countdown thanh toán -->
        <div class="whp-ty-card has-dropdowns">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-pay-num">2</span>
                    <div>
                        <h3><?php esc_html_e('Countdown thanh toán','whp'); ?></h3>
                        <p><?php esc_html_e('Đếm ngược thời gian để tạo áp lực hoàn thành đơn.','whp'); ?></p>
                    </div>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon rose"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Bật đồng hồ đếm ngược','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Hiển thị bộ đếm ngược thời gian thanh toán','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_countdown_enable" value="1" <?php checked($whp_woo_thankyou_countdown_enable,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
                <?php
                $_cd_opts = [
                    '15'   => ['label' => '15 phút', 'dot' => '#ef4444'],
                    '30'   => ['label' => '30 phút', 'dot' => '#f97316'],
                    '60'   => ['label' => '1 giờ',   'dot' => '#3b82f6'],
                    '1440' => ['label' => '24 giờ',  'dot' => '#22c55e'],
                ];
                $_cd_cur = $whp_woo_thankyou_countdown_minutes ?: '30';
                ?>
                <div class="whp-ty-field" style="align-items:center;gap:12px;border-bottom:none;padding-bottom:0">
                    <span class="whp-ty-field-icon" style="background:#f8fafc;color:#64748b"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span>
                    <div style="flex:1"><div class="whp-ty-field-label"><?php esc_html_e('Thời gian giới hạn','whp'); ?></div></div>
                    <div class="whp-cd-sel" id="whp-cd-sel">
                        <input type="hidden" name="whp_woo_thankyou_countdown_minutes" id="whp-cd-val" value="<?php echo esc_attr($_cd_cur); ?>">
                        <button type="button" class="whp-cd-sel-btn" id="whp-cd-btn">
                            <span class="whp-cd-dot" id="whp-cd-dot" style="background:<?php echo esc_attr($_cd_opts[$_cd_cur]['dot'] ?? '#f97316'); ?>"></span>
                            <span id="whp-cd-lbl"><?php echo esc_html($_cd_opts[$_cd_cur]['label'] ?? '30 phút'); ?></span>
                            <svg class="whp-cd-chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="whp-cd-panel" id="whp-cd-panel">
                            <?php foreach ($_cd_opts as $_cdv => $_cdo) : ?>
                            <div class="whp-cd-opt <?php echo ($_cd_cur === $_cdv) ? 'selected' : ''; ?>"
                                 data-val="<?php echo esc_attr($_cdv); ?>"
                                 data-dot="<?php echo esc_attr($_cdo['dot']); ?>"
                                 data-label="<?php echo esc_attr($_cdo['label']); ?>">
                                <span class="whp-cd-dot" style="background:<?php echo esc_attr($_cdo['dot']); ?>"></span>
                                <?php echo esc_html($_cdo['label']); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="whp-ty-note">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10" fill="#f1f5f9"/><path d="M12 8v4m0 4h.01" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg>
                    <span><?php esc_html_e('Sau khi hết thời gian, đơn hàng có thể tự động hủy nếu chưa thanh toán.','whp'); ?></span>
                </div>
            </div>
        </div>

        <!-- 3. Xác nhận chuyển khoản -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-pay-num">3</span>
                    <div>
                        <h3><?php esc_html_e('Xác nhận chuyển khoản','whp'); ?></h3>
                        <p><?php esc_html_e('Cho phép khách thông báo đã chuyển khoản, admin nhận cảnh báo ngay','whp'); ?></p>
                    </div>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Hiển thị nút "Tôi đã chuyển khoản"','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Thêm nút để khách xác nhận đã thanh toán','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_transfer_btn" value="1" <?php checked($whp_woo_thankyou_transfer_btn,'1'); ?> id="whp-transfer-toggle"><span class="whp-ty-slider"></span></label>
                </div>
                <div id="whp-transfer-sub" style="display:<?php echo $whp_woo_thankyou_transfer_btn ? 'block':'none'; ?>">
                    <div class="whp-ty-field" style="padding-left:0">
                        <span class="whp-ty-field-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
                        <div><div class="whp-ty-field-label"><?php esc_html_e('Thông báo qua Email','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Gửi email tới admin khi khách xác nhận đã chuyển','whp'); ?></div></div>
                        <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_transfer_email" value="1" <?php checked($whp_woo_thankyou_transfer_email,'1'); ?>><span class="whp-ty-slider"></span></label>
                    </div>
                    <div style="margin:8px 0 0;padding:9px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;font-size:12px;color:#1e40af;display:flex;gap:8px;align-items:flex-start">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span><?php printf( wp_kses( __('<strong>Email cơ bản:</strong> chỉ gửi đúng lúc khách bấm "Tôi đã chuyển khoản" — không qua AI. Để nhận thêm cảnh báo rủi ro, xác minh thành công, đơn mới... hãy cấu hình thêm ở <strong>AI Thanh Toán → Thông báo</strong>.', 'whp'), ['strong'=>[]] ) ); ?></span>
                    </div>
                </div>
                <div class="whp-ty-note">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10" fill="#f1f5f9"/><path d="M12 8v4m0 4h.01" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg>
                    <span><?php esc_html_e('Khi khách bấm "Tôi đã chuyển khoản": hệ thống ghi chú trên đơn hàng và tùy chọn gửi Email tới admin.','whp'); ?></span>
                </div>
            </div>
        </div>

    </div><!-- end payment tab -->

    <!-- ============================================================ -->
    <!-- TAB: NÂNG CAO                                                -->
    <!-- ============================================================ -->
    <div class="whp-ty-tab-content" data-content="advanced" style="display:none">

        <!-- Timeline -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-pay-num">1</span>
                    <div><h3><?php esc_html_e('Thanh trạng thái đơn hàng','whp'); ?></h3><p><?php esc_html_e('Hiển thị timeline tiến trình xử lý đơn hàng','whp'); ?></p></div>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="3" cy="12" r="2"/><line x1="5" y1="12" x2="7" y2="12"/><circle cx="9" cy="12" r="2"/><line x1="11" y1="12" x2="13" y2="12"/><circle cx="15" cy="12" r="2"/><line x1="17" y1="12" x2="19" y2="12"/><circle cx="21" cy="12" r="2"/></svg></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Hiển thị timeline đơn hàng','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Thanh trạng thái: Đặt hàng → Xác nhận → Vận chuyển → Giao hàng','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_show_timeline" value="1" <?php checked($whp_woo_thankyou_show_timeline,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
            </div>
        </div>

        <!-- Action buttons -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-pay-num">2</span>
                    <div><h3><?php esc_html_e('Hành động khách hàng','whp'); ?></h3><p><?php esc_html_e('Các nút CTA hiển thị trên trang cảm ơn sau đặt hàng','whp'); ?></p></div>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon green"><i class="dashicons dashicons-store" style="font-size:16px;width:16px;height:16px;line-height:1"></i></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Nút Tiếp tục mua hàng','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Dẫn khách quay lại cửa hàng để tiếp tục mua sắm','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_btn_continue" value="1" <?php checked($whp_woo_thankyou_btn_continue,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon blue"><i class="dashicons dashicons-format-chat" style="font-size:16px;width:16px;height:16px;line-height:1"></i></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Nút Liên hệ hỗ trợ','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Liên kết nhanh tới trang liên hệ hoặc chat hỗ trợ','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_btn_contact" value="1" <?php checked($whp_woo_thankyou_btn_contact,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>

                <!-- ===== Kênh liên hệ ===== -->
                <div class="whp-ty-ch-section" id="whp-contact-channels-wrap" style="<?php echo $whp_woo_thankyou_btn_contact ? '' : 'display:none'; ?>">
                    <div class="whp-ty-ch-section-head">
                        <p class="whp-ty-ch-section-title"><?php esc_html_e('Kênh liên hệ','whp'); ?></p>
                        <p class="whp-ty-ch-section-desc"><?php esc_html_e('Bật/tắt và tùy chỉnh các kênh liên hệ hiển thị trong popup.','whp'); ?></p>
                    </div>
                    <!-- Zalo -->
                    <div class="whp-ty-ch-row">
                        <span class="whp-ty-ch-icon" style="background:#e6f7ff;color:#0068ff">
                            <svg width="20" height="20" viewBox="0 0 48 48" fill="currentColor"><path d="M24 4C13 4 4 12.95 4 24c0 5.3 1.9 10.1 5 13.8L4 44l6.5-4.8C13.9 41.5 18.8 43 24 43c11 0 20-8.95 20-19S35 4 24 4z"/><path fill="#fff" d="M14.5 28.5l5-7.5 3.2 4.3 4.3-5.8 5 8H14.5z"/></svg>
                        </span>
                        <span class="whp-ty-ch-name">Zalo</span>
                        <span class="whp-ty-toggle-wrap"><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_contact_zalo_en" value="1" <?php checked($whp_contact_zalo_en,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                        <div class="whp-ty-ch-input-wrap">
                            <span class="whp-ty-ch-input-lbl"><?php esc_html_e('Nhập số Zalo','whp'); ?></span>
                            <input type="text" class="whp-ty-ch-input-val" name="whp_woo_thankyou_contact_zalo_val" value="<?php echo esc_attr($whp_contact_zalo_val); ?>" placeholder="0901234567">
                        </div>
                    </div>
                    <!-- Facebook -->
                    <div class="whp-ty-ch-row">
                        <span class="whp-ty-ch-icon" style="background:#e8f0fe;color:#1877f2">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </span>
                        <span class="whp-ty-ch-name">Facebook</span>
                        <span class="whp-ty-toggle-wrap"><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_contact_fb_en" value="1" <?php checked($whp_contact_fb_en,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                        <div class="whp-ty-ch-input-wrap">
                            <span class="whp-ty-ch-input-lbl"><?php esc_html_e('Nhập link Facebook','whp'); ?></span>
                            <input type="text" class="whp-ty-ch-input-val" name="whp_woo_thankyou_contact_fb_val" value="<?php echo esc_attr($whp_contact_fb_val); ?>" placeholder="https://facebook.com/...">
                        </div>
                    </div>
                    <!-- Messenger -->
                    <div class="whp-ty-ch-row">
                        <span class="whp-ty-ch-icon" style="background:#f3e8ff;color:#a855f7">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.145 2 11.243c0 3.063 1.53 5.789 3.924 7.565v3.692l3.579-1.966C10.287 20.836 11.128 21 12 21c5.523 0 10-4.145 10-9.757C22 6.145 17.523 2 12 2zm1 13.143l-2.548-2.714-4.973 2.714 5.468-5.802 2.61 2.714 4.912-2.714-5.47 5.802z"/></svg>
                        </span>
                        <span class="whp-ty-ch-name">Messenger</span>
                        <span class="whp-ty-toggle-wrap"><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_contact_msg_en" value="1" <?php checked($whp_contact_msg_en,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                        <div class="whp-ty-ch-input-wrap">
                            <span class="whp-ty-ch-input-lbl"><?php esc_html_e('Nhập link Messenger','whp'); ?></span>
                            <input type="text" class="whp-ty-ch-input-val" name="whp_woo_thankyou_contact_msg_val" value="<?php echo esc_attr($whp_contact_msg_val); ?>" placeholder="https://m.me/...">
                        </div>
                    </div>
                    <!-- Email -->
                    <div class="whp-ty-ch-row">
                        <span class="whp-ty-ch-icon" style="background:#fee2e2;color:#ef4444">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </span>
                        <span class="whp-ty-ch-name">Email</span>
                        <span class="whp-ty-toggle-wrap"><label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_contact_email_en" value="1" <?php checked($whp_contact_email_en,'1'); ?>><span class="whp-ty-slider"></span></label></span>
                        <div class="whp-ty-ch-input-wrap">
                            <span class="whp-ty-ch-input-lbl"><?php esc_html_e('Nhập email','whp'); ?></span>
                            <input type="text" class="whp-ty-ch-input-val" name="whp_woo_thankyou_contact_email_val" value="<?php echo esc_attr($whp_contact_email_val); ?>" placeholder="support@example.com">
                        </div>
                    </div>
                </div>

                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon rose"><i class="dashicons dashicons-clipboard" style="font-size:16px;width:16px;height:16px;line-height:1"></i></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Nút Xem chi tiết đơn hàng','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Dẫn khách tới trang chi tiết đơn hàng trong tài khoản','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_btn_view_order" value="1" <?php checked($whp_woo_thankyou_btn_view_order,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
                <div class="whp-ty-field">
                    <span class="whp-ty-field-icon" style="background:#fff7ed;color:#ea580c"><i class="dashicons dashicons-pdf" style="font-size:16px;width:16px;height:16px;line-height:1"></i></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Nút Tải hóa đơn PDF','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Xuất và tải xuống hóa đơn PDF của đơn hàng','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_btn_invoice" value="1" <?php checked($whp_woo_thankyou_btn_invoice,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
            </div>
        </div>

        <!-- Trust badges -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-pay-num">3</span>
                    <div><h3><?php esc_html_e('Khối tin cậy (Trust Badges)','whp'); ?></h3><p><?php esc_html_e('Hiển thị các badge bảo mật và chính sách để tạo niềm tin','whp'); ?></p></div>
                </div>
                <div class="whp-ty-field" style="margin-bottom:10px">
                    <span class="whp-ty-field-icon" style="background:#f5f3ff;color:#9333ea"><i class="dashicons dashicons-shield-alt" style="font-size:16px;width:16px;height:16px;line-height:1"></i></span>
                    <div><div class="whp-ty-field-label"><?php esc_html_e('Hiển thị trust badges','whp'); ?></div><div class="whp-ty-field-desc"><?php esc_html_e('Thêm dải badge bảo mật, chính sách hoàn trả, hỗ trợ 24/7','whp'); ?></div></div>
                    <label class="whp-ty-switch"><input type="checkbox" name="whp_woo_thankyou_trust_badges" value="1" <?php checked($whp_woo_thankyou_trust_badges,'1'); ?>><span class="whp-ty-slider"></span></label>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px">
                    <?php
                    $badges = [
                        ['color'=>'#22c55e','icon'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>','label'=>__('Thanh toán bảo mật','whp')],
                        ['color'=>'#3b82f6','icon'=>'<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>','label'=>__('Miễn phí vận chuyển','whp')],
                        ['color'=>'#f97316','icon'=>'<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/>','label'=>__('Đổi trả 30 ngày','whp')],
                        ['color'=>'#9333ea','icon'=>'<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.99 12 19.79 19.79 0 0 1 1.97 3.4 2 2 0 0 1 3.95 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>','label'=>__('Hỗ trợ 24/7','whp')],
                    ];
                    foreach ($badges as $b) : ?>
                    <div style="display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:6px;background:#f8fafc;border:1px solid #e2e8f0;font-size:11.5px;color:#374151;font-weight:500">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($b['color']); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $b['icon']; ?></svg>
                        <?php echo esc_html($b['label']); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div><!-- end advanced tab -->

    <!-- ============================================================ -->
    <!-- TAB: AI TÍCH HỢP                                             -->
    <!-- ============================================================ -->
    <div class="whp-ty-tab-content" data-content="ai" style="display:none">

        <!-- STATUS HEADER -->
        <div class="whp-ty-card" style="margin-bottom:16px">
            <div class="whp-ty-card-inner">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
                    <div style="display:flex;align-items:center;gap:14px">
                        <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,#e11d48,#be123c);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(225,29,72,.3)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/></svg>
                        </div>
                        <div>
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                                <span style="font-size:15px;font-weight:700;color:#0f172a"><?php esc_html_e('AI Thanh Toán','whp'); ?></span>
                                <?php if ($_ai_any_active) : ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:600;background:#dcfce7;color:#16a34a">
                                    <svg width="7" height="7" viewBox="0 0 10 10" fill="#16a34a"><circle cx="5" cy="5" r="5"/></svg>
                                    <?php esc_html_e('Đang hoạt động','whp'); ?>
                                </span>
                                <?php else : ?>
                                <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:600;background:#f1f5f9;color:#94a3b8">
                                    <svg width="7" height="7" viewBox="0 0 10 10" fill="#94a3b8"><circle cx="5" cy="5" r="5"/></svg>
                                    <?php esc_html_e('Chưa kích hoạt','whp'); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:12.5px;color:#64748b"><?php esc_html_e('Xác minh chuyển khoản, phát hiện gian lận và thông báo đa kênh bằng AI.','whp'); ?></div>
                        </div>
                    </div>
                    <!-- Connection chips -->
                    <div class="whp-ai-conn-chips">
                        <span class="whp-ai-conn-chip <?= $_ai_enable ? 'ok' : '' ?>">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><?= $_ai_enable ? '<polyline points="20 6 9 17 4 12"/>' : '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>' ?></svg>
                            <?php esc_html_e('Module AI','whp'); ?>
                        </span>
                        <span class="whp-ai-conn-chip <?= $_ai_has_key ? 'ok' : 'warn' ?>">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><?= $_ai_has_key ? '<polyline points="20 6 9 17 4 12"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>' ?></svg>
                            <?php echo $_ai_has_key ? esc_html( $_ai_connected_label ) : esc_html__( 'Chưa có API Key', 'whp' ); ?>
                        </span>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment')); ?>" class="whp-ai-conn-chip" style="text-decoration:none;cursor:pointer">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            <?php esc_html_e('Cài đặt AI','whp'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- FEATURE GRID -->
        <div class="whp-ai-feat-grid">

            <div class="whp-ai-feat-card <?= $_feat_ocr ? 'is-on' : '' ?>">
                <div class="whp-ai-feat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $_feat_ocr ? '#16a34a' : '#94a3b8' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div class="whp-ai-feat-name"><?php esc_html_e('OCR Biên lai','whp'); ?></div>
                <div class="whp-ai-feat-desc"><?php esc_html_e('Trích xuất tự động thông tin từ ảnh biên lai chuyển khoản.','whp'); ?></div>
                <?php echo whp_ai_badge($_feat_ocr, __('Đang chạy','whp'), __('Chưa bật','whp')); ?>
            </div>

            <div class="whp-ai-feat-card <?= $_feat_verify ? 'is-on' : '' ?>">
                <div class="whp-ai-feat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $_feat_verify ? '#16a34a' : '#94a3b8' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                </div>
                <div class="whp-ai-feat-name"><?php esc_html_e('Xác minh tức thì','whp'); ?></div>
                <div class="whp-ai-feat-desc"><?php esc_html_e('So khớp số tiền và nội dung CK với đơn hàng trong &lt; 5 giây.','whp'); ?></div>
                <?php echo whp_ai_badge($_feat_verify, __('Đang chạy','whp'), __('Chưa bật','whp')); ?>
            </div>

            <div class="whp-ai-feat-card <?= $_feat_fraud ? 'is-on' : '' ?>">
                <div class="whp-ai-feat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $_feat_fraud ? '#16a34a' : '#94a3b8' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="whp-ai-feat-name"><?php esc_html_e('Phát hiện gian lận','whp'); ?></div>
                <div class="whp-ai-feat-desc"><?php esc_html_e('AI cảnh báo thanh toán giả mạo hoặc biên lai chỉnh sửa.','whp'); ?></div>
                <?php echo whp_ai_badge($_feat_fraud, __('Đang chạy','whp'), __('Chưa bật','whp')); ?>
            </div>

            <div class="whp-ai-feat-card <?= $_feat_notify ? 'is-on' : '' ?>">
                <div class="whp-ai-feat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $_feat_notify ? '#16a34a' : '#94a3b8' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="whp-ai-feat-name"><?php esc_html_e('Thông báo đa kênh','whp'); ?></div>
                <div class="whp-ai-feat-desc"><?php esc_html_e('Discord, Webhook, Email — nhận ngay khi có CK.','whp'); ?></div>
                <?php echo whp_ai_badge($_feat_notify, __('Đã cấu hình','whp'), __('Chưa cấu hình','whp')); ?>
            </div>

            <div class="whp-ai-feat-card <?= $_feat_copilot ? 'is-on' : '' ?>">
                <div class="whp-ai-feat-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?= $_feat_copilot ? '#16a34a' : '#94a3b8' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div class="whp-ai-feat-name"><?php esc_html_e('AI Copilot','whp'); ?></div>
                <div class="whp-ai-feat-desc"><?php esc_html_e('Trợ lý AI phân tích xu hướng thanh toán và gợi ý tối ưu.','whp'); ?></div>
                <?php echo whp_ai_badge($_feat_copilot, __('Đang chạy','whp'), __('Chưa bật','whp')); ?>
            </div>

            <!-- Ô thứ 6: CTA Nâng cấp AI Premium -->
            <a href="<?php echo esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment')); ?>" class="whp-ai-feat-card whp-ai-upgrade-card" style="text-decoration:none;display:block;background:linear-gradient(135deg,#fff1f2,#ffe4e6);border-color:#fecdd3;border-left-color:#e11d48">
                <div class="whp-ai-feat-icon" style="background:linear-gradient(135deg,#e11d48,#be123c)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/></svg>
                </div>
                <div class="whp-ai-feat-name" style="color:#be123c"><?php esc_html_e('Nâng cấp AI Premium','whp'); ?></div>
                <div class="whp-ai-feat-desc" style="color:#9f1239"><?php esc_html_e('OCR không giới hạn, fraud score nâng cao, đối soát ngân hàng tự động và AI Copilot chuyên sâu.','whp'); ?></div>
                <span style="display:inline-flex;align-items:center;gap:5px;margin-top:2px;font-size:11.5px;font-weight:700;color:#e11d48"><?php esc_html_e('Nâng cấp ngay','whp'); ?> &#8594;</span>
            </a>

        </div><!-- .whp-ai-feat-grid -->

    </div><!-- end ai tab content -->

    <!-- ============================================================ -->
    <!-- TAB: HƯỚNG DẪN SỬ DỤNG (static docs)                        -->
    <!-- ============================================================ -->
    <div class="whp-ty-tab-content" data-content="guide" style="display:none">

        <!-- Header banner -->
        <div class="whp-guide-banner" style="margin-bottom:20px">
            <div class="whp-guide-banner-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            </div>
            <div>
                <div class="whp-guide-banner-title"><?php esc_html_e('Hướng dẫn sử dụng','whp'); ?></div>
                <div class="whp-guide-banner-sub"><?php esc_html_e('Tài liệu từng tab — giúp bạn cấu hình trang cảm ơn nhanh và đúng.','whp'); ?></div>
            </div>
            <div style="margin-left:auto;display:flex;flex-wrap:wrap;gap:6px;align-items:center;padding-left:8px">
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:20px;font-size:11px;font-weight:600;background:#dbeafe;color:#1d4ed8;white-space:nowrap">
                    <span style="width:6px;height:6px;border-radius:50%;background:#2563eb;display:inline-block"></span><?php esc_html_e('Tổng quan','whp'); ?>
                </span>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:20px;font-size:11px;font-weight:600;background:#ede9fe;color:#6d28d9;white-space:nowrap">
                    <span style="width:6px;height:6px;border-radius:50%;background:#7c3aed;display:inline-block"></span><?php esc_html_e('Giao diện','whp'); ?>
                </span>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:20px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d;white-space:nowrap">
                    <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block"></span><?php esc_html_e('Thanh toán','whp'); ?>
                </span>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:20px;font-size:11px;font-weight:600;background:#ffedd5;color:#c2410c;white-space:nowrap">
                    <span style="width:6px;height:6px;border-radius:50%;background:#ea580c;display:inline-block"></span><?php esc_html_e('Nâng cao','whp'); ?>
                </span>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:20px;font-size:11px;font-weight:600;background:#f1f5f9;color:#334155;white-space:nowrap">
                    <span style="width:6px;height:6px;border-radius:50%;background:#475569;display:inline-block"></span><?php esc_html_e('AI tích hợp','whp'); ?>
                </span>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

            <!-- TỔNG QUAN -->
            <div class="whp-ty-card whp-guide-sec-card" style="border-top-color:#2563eb">
                <div class="whp-ty-card-inner">
                    <div class="whp-guide-sec-hd">
                        <div class="whp-guide-sec-icon" style="background:#dbeafe">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                        </div>
                        <div>
                            <div class="whp-guide-sec-title"><?php esc_html_e('Tổng quan','whp'); ?></div>
                            <div class="whp-guide-sec-sub"><?php esc_html_e('Cài đặt chung trang cảm ơn','whp'); ?></div>
                        </div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#2563eb"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Bật trang cảm ơn tùy chỉnh','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Toggle đầu tiên trong Tổng quan — bật để kích hoạt toàn bộ tính năng thay thế trang mặc định WooCommerce.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#2563eb"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Timeline đơn hàng','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Hiển thị tiến trình: Đặt hàng → Xác nhận → Vận chuyển → Giao hàng. Tăng niềm tin của khách sau khi đặt.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#2563eb"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Nút hành động (CTA)','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Bật nút Tiếp tục mua hàng, Liên hệ hỗ trợ, Xem đơn hàng, Tải hoá đơn PDF — giúp khách hàng dễ thao tác tiếp theo.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#2563eb"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Trust Badges','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Hiển thị badge bảo mật thanh toán, chính sách hoàn trả, hỗ trợ 24/7 — tăng độ tin cậy ngay sau khi đặt hàng.','whp'); ?></div></div>
                    </div>
                </div>
            </div>

            <!-- GIAO DIỆN -->
            <div class="whp-ty-card whp-guide-sec-card" style="border-top-color:#7c3aed">
                <div class="whp-ty-card-inner">
                    <div class="whp-guide-sec-hd">
                        <div class="whp-guide-sec-icon" style="background:#ede9fe">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                        </div>
                        <div>
                            <div class="whp-guide-sec-title"><?php esc_html_e('Giao diện','whp'); ?></div>
                            <div class="whp-guide-sec-sub"><?php esc_html_e('Tùy chỉnh giao diện trang cảm ơn','whp'); ?></div>
                        </div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#7c3aed"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Màu sắc & Banner','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Tùy chỉnh màu nền, màu chủ đạo và ảnh banner phù hợp nhận diện thương hiệu của shop.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#7c3aed"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Xem trước Desktop / Mobile','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Panel "Xem trước giao diện" bên phải cập nhật ngay khi bạn thay đổi. Chuyển Desktop ↔ Mobile để kiểm tra responsive.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#7c3aed"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Logo & Tiêu đề trang','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Upload logo shop và đặt tiêu đề/phụ đề hiển thị trên banner. Nên dùng ảnh nền trắng hoặc trong suốt.','whp'); ?></div></div>
                    </div>
                </div>
            </div>

            <!-- THANH TOÁN -->
            <div class="whp-ty-card whp-guide-sec-card" style="border-top-color:#16a34a">
                <div class="whp-ty-card-inner">
                    <div class="whp-guide-sec-hd">
                        <div class="whp-guide-sec-icon" style="background:#dcfce7">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        </div>
                        <div>
                            <div class="whp-guide-sec-title"><?php esc_html_e('Thanh toán','whp'); ?></div>
                            <div class="whp-guide-sec-sub"><?php esc_html_e('Cấu hình QR và chuyển khoản','whp'); ?></div>
                        </div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#16a34a"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('QR Code chuyển khoản tự động','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('QR tự động điền số tài khoản + số tiền đơn hàng. Khách chỉ cần quét và xác nhận — giảm sai sót nội dung CK.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#16a34a"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Thông tin tài khoản ngân hàng','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Điền STK, tên chủ tài khoản, ngân hàng và nội dung chuyển khoản mặc định trong tab Thanh toán.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#16a34a"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Nút sao chép nhanh','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Bật nút copy STK và nội dung chuyển khoản — giúp khách hàng thực hiện CK đúng nhanh hơn, giảm hỗ trợ sau đặt.','whp'); ?></div></div>
                    </div>
                </div>
            </div>

            <!-- NÂNG CAO -->
            <div class="whp-ty-card whp-guide-sec-card" style="border-top-color:#ea580c">
                <div class="whp-ty-card-inner">
                    <div class="whp-guide-sec-hd">
                        <div class="whp-guide-sec-icon" style="background:#fff7ed">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15"/></svg>
                        </div>
                        <div>
                            <div class="whp-guide-sec-title"><?php esc_html_e('Nâng cao','whp'); ?></div>
                            <div class="whp-guide-sec-sub"><?php esc_html_e('Tính năng tối ưu chuyển đổi','whp'); ?></div>
                        </div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#ea580c"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Thanh trạng thái đơn hàng','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Hiển thị timeline tiến trình đơn hàng: Đặt hàng → Xác nhận → Vận chuyển → Giao hàng.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#ea580c"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Hành động khách hàng (CTA)','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Nút Tiếp tục mua hàng và Liên hệ hỗ trợ — giữ chân khách và tăng tỉ lệ mua tiếp theo.','whp'); ?></div></div>
                    </div>
                    <div class="whp-guide-list-item">
                        <span class="whp-guide-dot" style="background:#ea580c"></span>
                        <div><div class="whp-guide-list-title"><?php esc_html_e('Khối tin cậy (Trust Badges)','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Badge Thanh toán bảo mật, Đổi trả 30 ngày, Hỗ trợ 24/7 — tạo niềm tin với khách hàng.','whp'); ?></div></div>
                    </div>
                </div>
            </div>

            <!-- AI TÍCH HỢP — full width -->
            <div class="whp-ty-card whp-guide-sec-card" style="grid-column:1/-1;border-top-color:#0f172a">
                <div class="whp-ty-card-inner">
                    <div class="whp-guide-sec-hd">
                        <div class="whp-guide-sec-icon" style="background:#f1f5f9">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/><path d="M19 14l.8 2.2L22 17l-2.2.8L19 20l-.8-2.2L16 17l2.2-.8L19 14z"/></svg>
                        </div>
                        <div>
                            <div class="whp-guide-sec-title"><?php esc_html_e('AI tích hợp','whp'); ?></div>
                            <div class="whp-guide-sec-sub"><?php esc_html_e('Kích hoạt AI Thanh Toán theo thứ tự 3 bước','whp'); ?></div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0 24px">
                        <div class="whp-guide-list-item">
                            <span class="whp-guide-dot" style="background:#0f172a"></span>
                            <div><div class="whp-guide-list-title"><?php esc_html_e('Bước 1 — Nhập API Key','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Vào tab AI tích hợp → Cài đặt AI → nhập OpenAI hoặc Gemini API key. Đây là điều kiện bắt buộc để các tính năng AI hoạt động.','whp'); ?></div></div>
                        </div>
                        <div class="whp-guide-list-item">
                            <span class="whp-guide-dot" style="background:#0f172a"></span>
                            <div><div class="whp-guide-list-title"><?php esc_html_e('Bước 2 — Bật module AI','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Bật toggle "Module AI" và chọn tính năng cần dùng: OCR biên lai, Xác minh tức thì, Phát hiện gian lận.','whp'); ?></div></div>
                        </div>
                        <div class="whp-guide-list-item">
                            <span class="whp-guide-dot" style="background:#0f172a"></span>
                            <div><div class="whp-guide-list-title"><?php esc_html_e('Bước 3 — Thông báo đa kênh','whp'); ?></div><div class="whp-guide-list-desc"><?php esc_html_e('Kết nối Discord webhook hoặc Email để nhận cảnh báo ngay khi AI phát hiện chuyển khoản hoặc gian lận.','whp'); ?></div></div>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /docs grid -->

    </div><!-- end guide tab -->

    </div><!-- .whp-ty-main-left -->

    <!-- RIGHT: shared sticky preview panel + per-tab guide sidebar -->
    <div class="whp-ty-main-right">
        <div class="whp-rp-panel">
            <div class="whp-rp-head">
                <span><?php esc_html_e('Xem trước giao diện', 'whp'); ?></span>
                <div class="whp-ty-preview-devtabs">
                    <button type="button" class="whp-ty-dev-tab active" data-rp-dev="desktop">Desktop</button>
                    <button type="button" class="whp-ty-dev-tab" data-rp-dev="mobile">Mobile</button>
                </div>
            </div>
            <div class="whp-rp-body"><?php echo $rp_markup; ?></div>
            <div class="whp-ty-preview-footer">
                <p><?php esc_html_e('Preview cập nhật khi bạn thay đổi cài đặt', 'whp'); ?></p>
            </div>
        </div>


    </div><!-- .whp-ty-main-right -->

    </div><!-- .whp-ty-main -->

    </div><!-- #whp-ty-content-wrap -->

    <!-- BOTTOM SAVE BAR (standalone, full width 1200px) -->
    <div class="whp-ty-bottom-save-bar" id="whp-ty-save-bar">
        <span class="whp-ty-save-hint">
            <span class="whp-ty-save-hint-ic">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="11" x2="12" y2="16"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            </span>
            <span class="whp-ty-save-hint-txt">
                <strong><?php esc_html_e('Lưu thay đổi cài đặt','whp'); ?></strong>
                <span><?php esc_html_e('Các thay đổi sẽ được áp dụng ngay sau khi lưu.','whp'); ?></span>
            </span>
        </span>
        <button type="submit" name="submit" class="whp-ty-btn-save">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?php esc_html_e('Lưu thay đổi cài đặt','whp'); ?>
        </button>
    </div>

    </form>

</div><!-- .whp-ty-dash -->

<script>
var whpWooI18n={on:'<?php echo esc_js(__("Bật","whp")); ?>',off:'<?php echo esc_js(__("Tắt","whp")); ?>',dangBat:'<?php echo esc_js(__("Đang bật","whp")); ?>',dangTat:'<?php echo esc_js(__("Đang tắt","whp")); ?>'};
function whpToast(msg, type) {
    var wrap = document.getElementById('whp-toast-wrap');
    if (!wrap) return;
    type = type || 'success';
    var icons = {success:'✓', error:'✗'};
    var t = document.createElement('div');
    t.className = 'whp-toast wt-' + type;
    t.innerHTML = '<div class="whp-toast-icon">' + (icons[type]||'✓') + '</div>'
                + '<span class="whp-toast-msg">' + msg + '</span>'
                + '<button class="whp-toast-close" onclick="this.closest(\'.whp-toast\').remove()">×</button>';
    wrap.appendChild(t);
    setTimeout(function(){ t.classList.add('wt-out'); setTimeout(function(){ t.remove(); }, 280); }, 3800);
}
(function () {
    /* ===== TABS ===== */
    function whpTyGoTab(name) {
        document.querySelectorAll('.whp-ty-tab').forEach(function(b) {
            b.classList.toggle('active', b.dataset.tab === name);
        });
        document.querySelectorAll('.whp-ty-tab-content').forEach(function(c) {
            c.style.display = c.dataset.content === name ? '' : 'none';
        });
        // AI + Guide: full width. Còn lại: hiện preview panel + guide sidebar
        var fullTabs = ['ai', 'guide'];
        var mainRight = document.querySelector('.whp-ty-main-right');
        var mainGrid  = document.querySelector('.whp-ty-main');
        var isFull    = fullTabs.indexOf(name) !== -1;
        if (mainRight) { mainRight.style.display = isFull ? 'none' : ''; }
        if (mainGrid)  { mainGrid.classList.toggle('is-full', isFull); }
        // Show/hide guide sidebar card theo tab
        document.querySelectorAll('.whp-ty-tab-guide').forEach(function(el) {
            el.style.display = (el.dataset.guideFor === name) ? '' : 'none';
        });
        try { localStorage.setItem('whp_ty_tab', name); } catch(e) {}
    }
    window.whpTyGoTab = whpTyGoTab;

    document.querySelectorAll('.whp-ty-tab').forEach(function(btn) {
        btn.addEventListener('click', function() { whpTyGoTab(this.dataset.tab); });
    });

    // Restore last tab
    try {
        var saved = localStorage.getItem('whp_ty_tab');
        if (saved) whpTyGoTab(saved);
    } catch(e) {}

    /* ===== Guide tab: live preview + style toggle ===== */
    function whpGuidePreview() {
        var stepsEl = document.getElementById('whp-gprev-steps');
        if (!stepsEl) return;
        var html = '';
        document.querySelectorAll('[data-guide-section]').forEach(function(sec) {
            var slug  = sec.dataset.guideSection;
            var label = sec.dataset.guideLabel;
            var steps = [];
            for (var i = 1; i <= 3; i++) {
                var ti = document.querySelector('[name="whp_guide_' + slug + '_step' + i + '_title"]');
                var di = document.querySelector('[name="whp_guide_' + slug + '_step' + i + '_desc"]');
                var t  = ti ? ti.value : '';
                var d  = di ? di.value : '';
                if (t || d) steps.push({t: t, d: d, n: i});
            }
            if (!steps.length) return;
            html += '<div class="whp-gprev-section">'
                  + '<div class="whp-gprev-sec-name">' + label + '</div>';
            steps.forEach(function(s) {
                html += '<div class="whp-guide-preview-step">'
                      + '<div class="whp-guide-preview-num">' + s.n + '</div>'
                      + '<div>'
                      + '<div class="whp-guide-preview-title">' + s.t.replace(/</g,'&lt;') + '</div>'
                      + '<div class="whp-guide-preview-desc">'  + s.d.replace(/</g,'&lt;').replace(/\n/g,'<br>') + '</div>'
                      + '</div></div>';
            });
            html += '</div>';
        });
        var empty = stepsEl.querySelector('.whp-gprev-empty');
        if (html) {
            stepsEl.innerHTML = html;
        } else {
            if (!empty) stepsEl.innerHTML = '<p class="whp-gprev-empty" style="margin:0;font-size:12px;color:#94a3b8;text-align:center;padding:10px 0"><?php echo esc_js(__('Chưa có nội dung nào được điền', 'whp')); ?></p>';
        }
    }
    window.whpGuidePreview = whpGuidePreview;

    // Show/hide nút modal row khi đổi style
    document.querySelectorAll('input[name="whp_woo_thankyou_guide_style"]').forEach(function(r) {
        r.addEventListener('change', function() {
            var row = document.getElementById('whp-guide-btn-row');
            if (row) row.style.display = this.value === 'modal' ? '' : 'none';
        });
    });

    /* ===== Bật/Tắt LABEL bên trái MỌI toggle ===== */
    var MOD_SUB = {
        'whp_woo_thankyou_show_timeline':    ['<?php echo esc_js(__("Đang bật","whp")); ?>','<?php echo esc_js(__("Đang tắt","whp")); ?>'],
        'whp_woo_thankyou_show_qr_large':    ['QR lớn','Đang tắt'],
        'whp_woo_thankyou_countdown_enable': ['<?php echo esc_js(__("Đang bật","whp")); ?>','<?php echo esc_js(__("Đang tắt","whp")); ?>'],
        'whp_woo_thankyou_trust_badges':     ['<?php echo esc_js(__("Đang bật","whp")); ?>','<?php echo esc_js(__("Đang tắt","whp")); ?>'],
        'whp_woo_thankyou_transfer_btn':     ['<?php echo esc_js(__("Đang bật","whp")); ?>','<?php echo esc_js(__("Đang tắt","whp")); ?>'],
        'whp_woo_thankyou_guide_enable':     ['<?php echo esc_js(__("Đang bật","whp")); ?>','<?php echo esc_js(__("Đang tắt","whp")); ?>']
    };
    function syncToggleTxt(input) {
        var wrap = input.closest('.whp-ty-toggle-wrap');
        if (!wrap) return;
        var t = wrap.querySelector('.whp-ty-toggle-txt');
        if (t) { t.textContent = input.checked ? whpWooI18n.on : whpWooI18n.off; t.classList.toggle('on', input.checked); }
    }

    document.querySelectorAll('.whp-ty-switch').forEach(function(sw) {
        var input = sw.querySelector('input[type="checkbox"]');
        if (!input || (sw.parentNode && sw.parentNode.classList.contains('whp-ty-toggle-wrap'))) return;
        // Header toggle handled separately above
        if (input.id === 'whp-main-enable') return;
        var wrap = document.createElement('span');
        wrap.className = 'whp-ty-toggle-wrap';
        sw.parentNode.insertBefore(wrap, sw);
        var t = document.createElement('span');
        t.className = 'whp-ty-toggle-txt' + (input.checked ? ' on' : '');
        t.textContent = input.checked ? whpWooI18n.on : whpWooI18n.off;
        wrap.appendChild(t);
        wrap.appendChild(sw);
    });

    /* ===== COLOR CHANGE ===== */
    function whpColorChange(input) {
        var val = input.value;
        var hexMap = {blue:'#3b82f6',green:'#22c55e',orange:'#f97316',purple:'#9333ea',red:'#ef4444',dark:'#1f2937'};
        var hex = hexMap[val] || (document.getElementById('whp-color-text-input') || {}).value || '#3b82f6';

        document.querySelectorAll('[data-swatch][data-swatch-color]').forEach(function(s) {
            var c = s.dataset.swatchColor;
            var on = s.dataset.swatch === val;
            s.classList.toggle('active', on);
            s.style.setProperty('--sw-c', c);
        });

        // If custom selected, update hex from text input
        if (val === 'custom') {
            var ct2 = document.getElementById('whp-color-text-input');
            if (ct2 && ct2.value) hex = ct2.value;
        }

        // Update text input placeholder highlight
        var ct = document.getElementById('whp-color-text-input');
        if (ct && val !== 'custom') { /* keep value but don't overwrite custom field */ }

        updatePreviewAccent(hex);
        // Update preview square
        var prev1 = document.getElementById('whp-color1-preview');
        if (prev1) prev1.style.background = hex;
    }
    window.whpColorChange = whpColorChange;

    function updatePreviewAccent(hex) {
        document.querySelectorAll('.whp-thankyou-wrap').forEach(function(r){ r.style.setProperty('--whp-accent', hex); });
    }

    // color1 text input
    var ct = document.getElementById('whp-color-text-input');
    if (ct) {
        ct.addEventListener('input', function() {
            var hex = this.value;
            if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
                updatePreviewAccent(hex);
                // sync `:root` cho Premium layout (có <style>:root{--whp-accent:...})
                document.documentElement.style.setProperty('--whp-accent', hex);
                var prev1 = document.getElementById('whp-color1-preview');
                if (prev1) prev1.style.background = hex;
                var custSwatch = document.querySelector('[data-swatch="custom"]');
                if (custSwatch) { custSwatch.style.background = hex; custSwatch.style.setProperty('--sw-c', hex); }
            }
        });
    }

    // Custom swatch (+) → mở native color picker, picker → fill hex + cập nhật preview
    var custSwatchSpan = document.querySelector('[data-swatch="custom"]');
    var colorPickerInp = document.getElementById('whp-color-picker-input');
    if (custSwatchSpan && colorPickerInp) {
        custSwatchSpan.addEventListener('click', function() {
            setTimeout(function(){ colorPickerInp.click(); }, 30);
        });
        colorPickerInp.addEventListener('input', function() {
            var hex = this.value;
            var ctInp = document.getElementById('whp-color-text-input');
            if (ctInp) { ctInp.value = hex; ctInp.dispatchEvent(new Event('input')); }
            var custRadio = document.querySelector('input[name="whp_woo_thankyou_color"][value="custom"]');
            if (custRadio) { custRadio.checked = true; whpColorChange(custRadio); }
        });
    }

    // Màu phụ custom (+)
    var c2Btn    = document.getElementById('whp-color2-cust-btn');
    var c2Picker = document.getElementById('whp-color2-picker');
    if (c2Btn && c2Picker) {
        c2Btn.addEventListener('click', function() { setTimeout(function(){ c2Picker.click(); }, 30); });
        c2Picker.addEventListener('input', function() {
            var hex = this.value;
            whpColor2Set(hex);
            c2Btn.style.background = hex;
        });
    }

    // Màu nền custom (+)
    var bgBtn    = document.getElementById('whp-bg-cust-btn');
    var bgPicker = document.getElementById('whp-bg-picker');
    if (bgBtn && bgPicker) {
        bgBtn.addEventListener('click', function() { setTimeout(function(){ bgPicker.click(); }, 30); });
        bgPicker.addEventListener('input', function() {
            var hex = this.value;
            whpBgSet(hex);
            bgBtn.style.background = hex;
        });
    }

    /* ===== COLOR2 (Màu phụ) ===== */
    function whpColor2Set(hex) {
        document.getElementById('whp-color2-text').value = hex;
        document.getElementById('whp-color2-preview').style.background = hex;
        document.querySelectorAll('[data-color2-swatch]').forEach(function(b) {
            var on = b.dataset.color2Swatch === hex;
            b.classList.toggle('active', on);
        });
        document.querySelectorAll('.whp-thankyou-wrap').forEach(function(r){ r.style.setProperty('--whp-accent2', hex); });
        document.documentElement.style.setProperty('--whp-accent2', hex);
    }
    window.whpColor2Set = whpColor2Set;
    function whpColor2Input(input) {
        var hex = input.value;
        if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
            document.getElementById('whp-color2-preview').style.background = hex;
            document.querySelectorAll('[data-color2-swatch]').forEach(function(b){ b.classList.remove('active'); });
            document.querySelectorAll('.whp-thankyou-wrap').forEach(function(r){ r.style.setProperty('--whp-accent2', hex); });
            document.documentElement.style.setProperty('--whp-accent2', hex);
        }
    }
    window.whpColor2Input = whpColor2Input;

    /* ===== BG COLOR (Màu nền) ===== */
    function whpBgSet(hex) {
        document.getElementById('whp-bg-text').value = hex;
        document.getElementById('whp-bg-preview').style.background = hex;
        document.querySelectorAll('[data-bg-swatch]').forEach(function(b) {
            var on = b.dataset.bgSwatch === hex;
            b.classList.toggle('active', on);
        });
        document.querySelectorAll('.whp-thankyou-wrap').forEach(function(r){ r.style.setProperty('--whp-bg', hex); });
        document.documentElement.style.setProperty('--whp-bg', hex);
    }
    window.whpBgSet = whpBgSet;
    function whpBgInput(input) {
        var hex = input.value;
        if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
            document.getElementById('whp-bg-preview').style.background = hex;
            document.querySelectorAll('[data-bg-swatch]').forEach(function(b){ b.classList.remove('active'); });
            document.querySelectorAll('.whp-thankyou-wrap').forEach(function(r){ r.style.setProperty('--whp-bg', hex); });
            document.documentElement.style.setProperty('--whp-bg', hex);
        }
    }
    window.whpBgInput = whpBgInput;

    /* ===== LAYOUT CARDS ===== */
    function whpSetLayout(label, val) {
        document.querySelectorAll('[data-layout-card]').forEach(function(el) {
            el.classList.remove('active');
        });
        label.classList.add('active');
        var r = label.querySelector('input[type="radio"]');
        if (r) r.checked = true;
        // Show/hide pre-rendered layout panels
        document.querySelectorAll('[data-rp-layout]').forEach(function(el){
            el.style.display = el.dataset.rpLayout === val ? 'block' : 'none';
        });
    }
    window.whpSetLayout = whpSetLayout;

    /* ===== DESIGN TOKEN CUSTOM DROPDOWNS → MỌI RICH PREVIEW ===== */
    function setRpVar(prop, value) {
        document.querySelectorAll('.whp-thankyou-wrap').forEach(function(r){ r.style.setProperty(prop, value); });
        document.documentElement.style.setProperty(prop, value);
    }
    var fontStack = <?php echo wp_json_encode($ty_font_stack); ?>;
    var shadowCss = <?php echo wp_json_encode($ty_shadow_css); ?>;
    var padCss    = <?php echo wp_json_encode($ty_pad_css); ?>;
    var gapCss    = <?php echo wp_json_encode(['compact'=>'12px','normal'=>'18px','relaxed'=>'24px']); ?>;

    // Apply preview for a setrow value change
    function applySetrowPreview(type, val) {
        if (type === 'font')    { setRpVar('--whp-font',   fontStack[val] || fontStack['inter']); }
        else if (type === 'radius')  { setRpVar('--whp-radius', (parseInt(val, 10) || 0) + 'px'); }
        else if (type === 'shadow')  { setRpVar('--whp-shadow', shadowCss[val] || shadowCss['md']); }
        else if (type === 'spacing') {
            setRpVar('--whp-pad', padCss[val] || padCss['relaxed']);
            setRpVar('--whp-gap', gapCss[val] || gapCss['relaxed']);
        }
    }

    // Toggle open/close setrow dropdown
    function openSetrow(row) {
        var allRows = document.querySelectorAll('.whp-ty-setrow[data-setrow]');
        allRows.forEach(function(r) { if (r !== row) r.classList.remove('open'); });
        row.classList.toggle('open');
    }

    // Wire setrow click (toggle open/close) — stop propagation on panel clicks
    document.querySelectorAll('.whp-ty-setrow[data-setrow]').forEach(function(row) {
        row.addEventListener('click', function(e) {
            // if click is inside a panel option, let the opt handler deal with it
            if (e.target.closest('.whp-ty-setrow-panel')) return;
            openSetrow(row);
        });
    });

    // Wire option clicks
    document.querySelectorAll('.whp-ty-setrow-opt').forEach(function(opt) {
        opt.addEventListener('click', function(e) {
            e.stopPropagation();
            var type = this.dataset.setrowOpt;
            var val  = this.dataset.val;
            var row  = this.closest('.whp-ty-setrow');
            if (!row) return;

            // Update hidden input
            var inp = document.getElementById('whp-set-' + type);
            if (inp) inp.value = val;

            // Update displayed value text
            var vEl = document.getElementById('setrow-' + type + '-val');
            if (vEl) vEl.textContent = this.textContent;

            // Update selected state in panel
            row.querySelectorAll('.whp-ty-setrow-opt').forEach(function(o){ o.classList.remove('selected'); });
            this.classList.add('selected');

            // Apply to preview
            applySetrowPreview(type, val);

            // Close the row
            row.classList.remove('open');
        });
    });

    // Close dropdowns on outside click or Esc
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.whp-ty-setrow[data-setrow]')) {
            document.querySelectorAll('.whp-ty-setrow[data-setrow].open').forEach(function(r){ r.classList.remove('open'); });
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.whp-ty-setrow[data-setrow].open').forEach(function(r){ r.classList.remove('open'); });
        }
    });

    /* ===== DEVICE TABS ===== */
    document.querySelectorAll('[data-rp-dev]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var panel = this.closest('.whp-rp-panel');
            if (!panel) return;
            panel.querySelectorAll('[data-rp-dev]').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            var wrap = panel.querySelector('.whp-thankyou-wrap');
            if (wrap) wrap.style.maxWidth = this.dataset.rpDev === 'mobile' ? '320px' : '440px';
        });
    });

    /* ===== TRANSFER SUB BLOCK ===== */
    function wireTransferToggle(id) {
        var t = document.getElementById(id);
        var s = document.getElementById('whp-transfer-sub');
        if (t && s) {
            t.addEventListener('change', function() { s.style.display = this.checked ? 'block' : 'none'; });
        }
    }
    wireTransferToggle('whp-transfer-toggle');
    wireTransferToggle('whp-transfer-toggle-ov');

    /* ===== CONTACT CHANNEL SECTION — show/hide theo toggle chính ===== */
    (function() {
        var channelWrap = document.getElementById('whp-contact-channels-wrap');
        if (!channelWrap) return;
        document.querySelectorAll('input[name="whp_woo_thankyou_btn_contact"]').forEach(function(inp) {
            inp.addEventListener('change', function() {
                channelWrap.style.display = this.checked ? '' : 'none';
            });
        });
    })();

    /* ===== MODULE STATE → MỌI RICH PREVIEW + ĐỒNG BỘ + CHẤM ===== */
    var RP_TOGGLE = {
        'whp_woo_thankyou_show_timeline':    'timeline',
        'whp_woo_thankyou_show_qr_large':    'qr',
        'whp_woo_thankyou_countdown_enable': 'countdown',
        'whp_woo_thankyou_trust_badges':     'trust',
        'whp_woo_thankyou_transfer_btn':     'transfer',
        'whp_woo_thankyou_btn_continue':     'btn-continue',
        'whp_woo_thankyou_btn_view_order':   'btn-view',
        'whp_woo_thankyou_btn_contact':      'btn-contact',
        'whp_woo_thankyou_btn_invoice':      'btn-invoice'
    };
    var RP_DISP = { trust: 'flex' }; // còn lại mặc định 'block'
    var OV_STATUS_ON = {
        'whp_woo_thankyou_show_timeline':    '<?php echo esc_js(__('Đang bật','whp')); ?>',
        'whp_woo_thankyou_show_qr_large':    '<?php echo esc_js(__('QR lớn','whp')); ?>',
        'whp_woo_thankyou_countdown_enable': '<?php echo esc_js(__('Đang bật','whp')); ?>',
        'whp_woo_thankyou_trust_badges':     '<?php echo esc_js(__('Đang bật','whp')); ?>',
        'whp_woo_thankyou_transfer_btn':     '<?php echo esc_js(__('Đang bật','whp')); ?>',
    };
    function applyModuleState(name, checked) {
        if (!name) return;
        // đồng bộ mọi checkbox cùng name + nhãn Bật/Tắt + chấm + sub-text lưới module
        document.querySelectorAll('input[type="checkbox"][name="' + CSS.escape(name) + '"]').forEach(function(other) {
            if (other.checked !== checked) other.checked = checked;
            syncToggleTxt(other);
            var mod = other.closest('.whp-ty-module');
            if (mod) {
                var dot = mod.querySelector('.whp-ty-module-dot, .whp-ty-module-icon');
                if (dot) { dot.classList.toggle('on', checked); dot.classList.toggle('off', !checked); }
                var sub = mod.querySelector('.whp-ty-module-sub');
                if (sub && MOD_SUB[name]) sub.textContent = checked ? MOD_SUB[name][0] : MOD_SUB[name][1];
            }
            // cập nhật status text + icon color trong lưới Overview
            var ovMod = other.closest('.whp-ov-module');
            if (ovMod) {
                var ovStatus = ovMod.querySelector('.whp-ov-mod-status');
                if (ovStatus && OV_STATUS_ON[name]) {
                    ovStatus.textContent = checked ? OV_STATUS_ON[name] : '<?php echo esc_js(__('Đang tắt','whp')); ?>';
                }
                var ovIcon = ovMod.querySelector('.whp-ov-mod-icon');
                if (ovIcon) { ovIcon.classList.toggle('green', checked); ovIcon.classList.toggle('grey', !checked); }
            }
        });
        // bật/tắt phần tử tương ứng trong TẤT CẢ rich preview (cả Tổng quan & Giao diện)
        var rpKey = RP_TOGGLE[name];
        if (rpKey) {
            var disp = RP_DISP[rpKey] || 'block';
            document.querySelectorAll('[data-rp="' + rpKey + '"]').forEach(function(el){ el.style.display = checked ? disp : 'none'; });
        }
    }
    document.querySelectorAll('input[type="checkbox"]').forEach(function(cb) {
        if (cb.id === 'whp-main-enable') return;
        cb.addEventListener('change', function() { applyModuleState(this.name, this.checked); });
    });

    /* ===== COUNTDOWN CHẠY THẬT (live) ===== */
    var cdTotal = <?php echo esc_js($cd_secs); ?>;
    var cdLeft  = cdTotal;
    function pad2(n){ return (n < 10 ? '0' : '') + n; }
    function fmtClock(s){ return pad2(Math.floor(s/3600)) + ':' + pad2(Math.floor((s%3600)/60)) + ':' + pad2(s%60); }
    function tickCountdown(){
        cdLeft = cdLeft <= 0 ? cdTotal : cdLeft - 1;
        var str = fmtClock(cdLeft);
        document.querySelectorAll('.whp-ty__countdown-clock').forEach(function(el){ el.textContent = str; });
    }
    setInterval(tickCountdown, 1000);
    /* ===== COUNTDOWN CUSTOM SELECT ===== */
    (function() {
        var sel = document.getElementById('whp-cd-sel');
        if (!sel) return;
        var btn    = document.getElementById('whp-cd-btn');
        var valInp = document.getElementById('whp-cd-val');
        var dot    = document.getElementById('whp-cd-dot');
        var lbl    = document.getElementById('whp-cd-lbl');
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.whp-ty-setrow[data-setrow].open').forEach(function(r){ r.classList.remove('open'); });
            sel.classList.toggle('open');
        });
        sel.querySelectorAll('.whp-cd-opt').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                valInp.value = this.dataset.val;
                dot.style.background = this.dataset.dot;
                lbl.textContent = this.dataset.label;
                sel.querySelectorAll('.whp-cd-opt').forEach(function(o){ o.classList.remove('selected'); });
                this.classList.add('selected');
                sel.classList.remove('open');
                cdTotal = (parseInt(this.dataset.val, 10) || 30) * 60;
                cdLeft  = cdTotal;
                tickCountdown();
            });
        });
        document.addEventListener('click', function(e) {
            if (!sel.contains(e.target)) sel.classList.remove('open');
        });
    }());

    /* ===== MAIN ENABLE HEADER TEXT ===== */
    var mainEn    = document.getElementById('whp-main-enable');
    var htxt      = document.getElementById('whp-main-htxt');
    var contentWrap = document.getElementById('whp-ty-content-wrap');
    var saveBar   = document.getElementById('whp-ty-save-bar');
    function updateMainToggleUI(doSave) {
        if (!mainEn || !htxt) return;
        var isOn = mainEn.checked;
        if (isOn) {
            htxt.textContent = '<?php echo esc_js(__('Đang bật','whp')); ?>';
            htxt.classList.add('whp-ty-htxt-on');
        } else {
            htxt.textContent = '<?php echo esc_js(__('Đang tắt','whp')); ?>';
            htxt.classList.remove('whp-ty-htxt-on');
        }
        // Dim content and hide save bar when disabled
        if (contentWrap) { contentWrap.classList.toggle('ty-disabled', !isOn); }
        if (saveBar)     { saveBar.style.display = isOn ? '' : 'none'; }
        // Auto-save on toggle change via AJAX
        if (doSave) {
            var _nonce = '<?php echo esc_js( wp_create_nonce('whp_thankyou_toggle') ); ?>';
            var _fd = new FormData();
            _fd.append('action', 'whp_thankyou_toggle_enable');
            _fd.append('nonce', _nonce);
            _fd.append('active', isOn ? '1' : '0');
            fetch(ajaxurl, { method: 'POST', body: _fd })
                .then(function(r){ return r.json(); })
                .then(function(r){
                    if (r.success) {
                        whpToast(isOn ? '<?php echo esc_js(__('Đã bật Trang đơn hàng', 'whp')); ?>' : '<?php echo esc_js(__('Đã tắt Trang đơn hàng', 'whp')); ?>', 'success');
                    } else {
                        whpToast('<?php echo esc_js(__('Lỗi lưu trạng thái', 'whp')); ?>', 'error');
                    }
                })
                .catch(function(){ whpToast('<?php echo esc_js(__('Lỗi kết nối', 'whp')); ?>', 'error'); });
        }
    }
    if (mainEn) mainEn.addEventListener('change', function() { updateMainToggleUI(true); });
    updateMainToggleUI(false);

    // Channel field format validation on form submit
    var tyForm = document.getElementById('whp-ty-form');
    if (tyForm) {
        tyForm.addEventListener('submit', function(e) {
            var phoneRe = /^(\+84|0)\d{8,10}$/;
            var urlRe   = /^https?:\/\/.{3,}/;
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
            var tyChFields = [
                {name:'whp_woo_thankyou_contact_zalo_val',  re:phoneRe, msg:'<?php echo esc_js(__('Số Zalo không hợp lệ. Ví dụ: 0901234567', 'whp')); ?>'},
                {name:'whp_woo_thankyou_contact_fb_val',    re:urlRe,   msg:'<?php echo esc_js(__('Link Facebook phải bắt đầu bằng https://', 'whp')); ?>'},
                {name:'whp_woo_thankyou_contact_msg_val',   re:urlRe,   msg:'<?php echo esc_js(__('Link Messenger phải bắt đầu bằng https://', 'whp')); ?>'},
                {name:'whp_woo_thankyou_contact_email_val', re:emailRe, msg:'<?php echo esc_js(__('Email không hợp lệ. Ví dụ: support@example.com', 'whp')); ?>'}
            ];
            var firstErr = null;
            tyChFields.forEach(function(f) {
                var inp = tyForm.querySelector('[name="' + f.name + '"]');
                if (!inp) return;
                var val = inp.value.trim();
                if (val && !f.re.test(val)) {
                    inp.style.borderColor = '#ef4444';
                    inp.style.boxShadow   = '0 0 0 2px rgba(239,68,68,0.2)';
                    if (!firstErr) firstErr = f.msg;
                } else {
                    inp.style.borderColor = '';
                    inp.style.boxShadow   = '';
                }
            });
            if (firstErr) {
                e.preventDefault();
                whpToast(firstErr, 'error');
            }
        });
        // Clear error styles on input
        tyForm.querySelectorAll('.whp-ty-ch-input-val').forEach(function(inp) {
            inp.addEventListener('input', function() {
                inp.style.borderColor = '';
                inp.style.boxShadow   = '';
            });
        });
    }
}());
</script>
