<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// ── Collect data for all modules ─────────────────────────────────────────────
$whp_setting = get_option( 'whp_setting', [] );
$opt = function( $key, $default = '' ) use ( $whp_setting ) {
    return isset( $whp_setting[ $key ] ) ? $whp_setting[ $key ] : $default;
};

// 1. Kênh liên hệ
$channels = [];
if ( !empty( $opt('whp_contact_other_zalo') )          && $opt('whp_contact_other_zalo_active', '1') !== '0' )    $channels[] = 'Zalo';
if ( !empty( $opt('whp_contact_other_facebook') )       && $opt('whp_contact_other_facebook_active', '1') !== '0' ) $channels[] = 'Facebook';
if ( !empty( $opt('whp_contact_other_facebook_page') )  && $opt('whp_contact_other_messenger_active', '1') !== '0' ) $channels[] = 'Messenger';
if ( !empty( $opt('whp_contact_other_email') )          && $opt('whp_contact_other_email_active', '1') !== '0' )    $channels[] = 'Email';
$phone_data = $opt('whp_contact_phone_data');
if ( !empty( $phone_data ) && is_array( $phone_data ) ) {
    foreach ( $phone_data as $ph ) {
        if ( !empty( $ph['phone'] ) ) { $channels[] = 'Hotline'; break; }
    }
}
$contact_on   = $opt('whp_contact_active') === '1';
$contact_count = count( $channels );

// 2. Header & Footer code
$code_zones = [];
if ( !empty( $opt('whp_code_header') ) ) $code_zones[] = 'Header';
if ( !empty( $opt('whp_code_body') ) )   $code_zones[] = 'Body';
if ( !empty( $opt('whp_code_footer') ) ) $code_zones[] = 'Footer';
$code_count = count( $code_zones );

// 3. Pop-up
$popup_active = $opt('whp_popup_active') === '1';
$popup_title  = trim( $opt('whp_popup_title') );
if ( empty( $popup_title ) ) $popup_title = __( 'Popup đăng ký', 'whp' );
$popup_type_map = [ '0' => __( 'Form đăng ký', 'whp' ), '1' => __( 'Banner ảnh', 'whp' ), '2' => __( 'Mạng xã hội', 'whp' ) ];
$popup_type_label = $popup_type_map[ $opt('whp_popup_type') ?: '0' ] ?? 'Popup';

// 4. Email & SMTP
$smtp_active    = $opt('whp_smtp_active') === '1';
$smtp_host      = trim( $opt('whp_smtp_host') );
$smtp_email     = trim( $opt('whp_smtp_email') );
$smtp_ok        = $smtp_active && !empty( $smtp_host );

// 5. AI Hub
$ai_providers = [
    'google'    => 'Google Gemini',
    'anthropic' => 'Anthropic Claude',
    'openai'    => 'OpenAI GPT',
];
$ai_connected_name = '';
foreach ( $ai_providers as $key => $label ) {
    if ( get_option( "wpaap_provider_connected_{$key}", 'no' ) === 'yes' ) {
        $ai_connected_name = $label;
        break;
    }
}
$ai_ok = !empty( $ai_connected_name );
$ai_posts = (int) get_option( 'wpaap_total_ai_posts', 0 );

// 6. Bảo vệ & Tối ưu
$sf      = get_option( 'wph_spam_filter_settings', [] );
$sf_keys = [ 'honeypot', 'rate_limit', 'temp_email', 'proxy_vpn', 'dnsbl', 'monitor_mode', 'code_detect' ];
$sf_on   = array_filter( $sf_keys, function( $k ) use ( $sf ) {
    return !empty( $sf[ $k ]['active'] ) && $sf[ $k ]['active'] === '1';
} );
$sf_count      = count( $sf_on );
$sf_total      = count( $sf_keys );
$maint_on      = $opt('whp_maintenance_active') === '1';
$sf_spam_count = isset( $sf['log'] ) ? count( (array) $sf['log'] ) : 0;

// Per-section feature counts (Bảo vệ Website / Tối ưu Website / Quản trị hệ thống)
$whp_setting = get_option( 'whp_setting', [] );
$sec1_keys = [
    'whp_security_remove_xmlrpc', 'whp_security_disable_copy', 'whp_security_delete_wphead',
    'whp_security_hide_wp_version', 'whp_security_hide_theme_plugin', 'whp_security_change_login_url',
];
$sec2_keys = [
    'whp_extention_duplicate_page_post', 'whp_extention_duplicate_menu', 'whp_extention_enable_404_redirect',
    'whp_extention_disable_emojis', 'whp_extention_remove_query_string', 'whp_extention_disbale_wp_embeds',
    'whp_extention_disbale_google_fonts', 'whp_extention_disable_heartbeat_frontend', 'whp_extention_heartbeat_limit_admin',
];
$sec3_keys = [
    'whp_extention_notification', 'whp_extention_disbale_dashicons',
    'whp_extention_custom_login_theme', 'whp_extention_svg',
];
$sec1_on    = count( array_filter( $sec1_keys, fn( $k ) => ( $whp_setting[ $k ] ?? '' ) === '1' ) );
$sec2_on    = count( array_filter( $sec2_keys, fn( $k ) => ( $whp_setting[ $k ] ?? '' ) === '1' ) );
$sec3_on    = count( array_filter( $sec3_keys, fn( $k ) => ( $whp_setting[ $k ] ?? '' ) === '1' ) );
$sec1_total = count( $sec1_keys );
$sec2_total = count( $sec2_keys );
$sec3_total = count( $sec3_keys );
$protect_ok = ( $sec1_on + $sec2_on + $sec3_on ) >= 4;

// 7. Cửa hàng nâng cao (WooCommerce)
$woo_active = class_exists( 'WooCommerce' );
$woo_ver    = $woo_active && defined( 'WC_VERSION' ) ? WC_VERSION : '';

// Total active features summary
$total_active = 0;
if ( $contact_on && $contact_count > 0 ) $total_active++;
if ( $code_count > 0 )   $total_active++;
if ( $popup_active )     $total_active++;
if ( $smtp_ok )          $total_active++;
if ( $ai_ok )            $total_active++;
if ( $protect_ok )       $total_active++;
if ( $woo_active )       $total_active++;
$total_modules = 7;

// ── Admin URL helper ──────────────────────────────────────────────────────────
$url = function( $slug ) { return admin_url( 'admin.php?page=' . $slug ); };

whp_get_shared( 'header' );
?>
<style>
.whp-dash{max-width:1080px;margin:0 auto;padding:0 0 40px;}

/* Hero */
.whp-dash-hero{position:relative;background:linear-gradient(100deg,#ffffff 0%,#f0f4ff 45%,#e8f0fd 100%);border-radius:20px;box-shadow:0 4px 24px rgba(56,88,233,0.1),0 0 0 1px #e0e7ff;margin-bottom:28px;overflow:hidden;min-height:168px;display:flex;align-items:stretch;}
.whp-dash-hero-left{position:relative;z-index:2;padding:32px 36px;display:flex;flex-direction:column;justify-content:center;gap:14px;max-width:500px;flex-shrink:0;}
.whp-dash-hero-right{position:absolute;inset:0 0 0 38%;overflow:hidden;pointer-events:none;}
.whp-dash-hero-title-row{display:flex;align-items:center;gap:14px;}
.whp-dash-hero-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#3858e9,#6b8af5);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 4px 12px rgba(56,88,233,0.3);}
.whp-dash-hero h1{font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;}
.whp-dash-hero-subtitle{color:#64748b;font-size:13.5px;line-height:1.6;margin:0;padding-left:58px;}

/* Modules grid */
.whp-dash-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
@media(max-width:900px){.whp-dash-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:580px){.whp-dash-grid{grid-template-columns:1fr;}}

/* Card */
.whp-dash-card{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:20px 20px 16px;display:flex;flex-direction:column;gap:0;transition:border-color .18s,box-shadow .18s;position:relative;overflow:hidden;}
.whp-dash-card:hover{border-color:#bfdbfe;box-shadow:0 4px 20px rgba(37,99,235,0.08);}
.whp-dash-card--active{border-color:#bbf7d0;}
.whp-dash-card--warn{border-color:#fde68a;}
.whp-dash-card--gray{border-color:#e2e8f0;}

/* Card top bar accent */
.whp-dash-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:16px 16px 0 0;}
.whp-dash-card--active::before{background:linear-gradient(90deg,#22c55e,#86efac);}
.whp-dash-card--warn::before{background:linear-gradient(90deg,#f59e0b,#fcd34d);}
.whp-dash-card--gray::before{background:#e2e8f0;}

.whp-dash-card-head{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;}
.whp-dash-card-icon{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.whp-dash-card-icon--green{background:#f0fdf4;color:#16a34a;}
.whp-dash-card-icon--amber{background:#fffbeb;color:#d97706;}
.whp-dash-card-icon--gray{background:#f8fafc;color:#94a3b8;}
.whp-dash-card-icon--blue{background:#eff6ff;color:#2563eb;}
.whp-dash-card-icon--purple{background:#faf5ff;color:#7c3aed;}
.whp-dash-card-icon--red{background:#fef2f2;color:#dc2626;}
.whp-dash-card-icon--teal{background:#f0fdfa;color:#0d9488;}

.whp-dash-badge{font-size:11px;font-weight:700;border-radius:20px;padding:3px 9px;line-height:1.6;white-space:nowrap;}
.whp-dash-badge--on{background:#dcfce7;color:#15803d;}
.whp-dash-badge--warn{background:#fef9c3;color:#a16207;}
.whp-dash-badge--off{background:#f1f5f9;color:#94a3b8;}
.whp-dash-badge--maint{background:#fef2f2;color:#dc2626;}

.whp-dash-card-name{font-size:14px;font-weight:700;color:#0f172a;margin:0 0 4px;}
.whp-dash-card-meta{font-size:12.5px;color:#64748b;line-height:1.5;flex:1;}
.whp-dash-card-meta strong{color:#1e293b;}

.whp-dash-card-footer{margin-top:14px;padding-top:12px;border-top:1px solid #f1f5f9;}
.whp-dash-btn{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:#2563eb;text-decoration:none;padding:7px 14px;background:#eff6ff;border-radius:8px;transition:background .15s,color .15s;}
.whp-dash-btn:hover{background:#dbeafe;color:#1d4ed8;}
.whp-dash-btn--gray{color:#64748b;background:#f8fafc;}
.whp-dash-btn--gray:hover{background:#e2e8f0;color:#334155;}

/* Maintenance alert bar */
.whp-dash-maint-bar{background:#fef2f2;border:1.5px solid #fecaca;border-radius:12px;padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;font-size:13px;color:#991b1b;}
.whp-dash-maint-bar a{color:#dc2626;font-weight:700;text-decoration:none;margin-left:auto;}
.whp-dash-maint-bar a:hover{text-decoration:underline;}
</style>

<div class="whp-dash">

<?php if ( $maint_on ) : ?>
<div class="whp-dash-maint-bar">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span><strong><?php esc_html_e( 'Chế độ bảo trì đang bật.', 'whp' ); ?></strong> <?php esc_html_e( 'Website của bạn đang ẩn với khách truy cập.', 'whp' ); ?></span>
    <a href="<?php echo esc_url( $url('mb-wphelper-security') ); ?>"><?php esc_html_e( 'Tắt ngay', 'whp' ); ?> &rarr;</a>
</div>
<?php endif; ?>

<!-- Hero -->
<div class="whp-dash-hero">
    <!-- Left: title + subtitle -->
    <div class="whp-dash-hero-left">
        <div class="whp-dash-hero-title-row">
            <div class="whp-dash-hero-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><line x1="8" y1="6" x2="21" y2="6" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/><line x1="8" y1="12" x2="21" y2="12" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/><line x1="8" y1="18" x2="21" y2="18" stroke="#fff" stroke-width="1.8" stroke-linecap="round"/><circle cx="3" cy="6" r="2" stroke="#fff" stroke-width="1.6"/><circle cx="3" cy="12" r="2" fill="#fff"/><circle cx="3" cy="18" r="2" stroke="#fff" stroke-width="1.6"/></svg>
            </div>
            <h1>WP Helper Premium</h1>
        </div>
        <p class="whp-dash-hero-subtitle"><?php esc_html_e( 'Hệ thống điều khiển — website cài đặt gì, plugin điều khiển cái đó.', 'whp' ); ?><br><?php esc_html_e( 'Chọn module bên dưới để cấu hình, bật/tắt hoặc kiểm tra trạng thái.', 'whp' ); ?></p>
    </div>
    <!-- Right: dashboard illustration SVG -->
    <div class="whp-dash-hero-right">
        <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
            <defs>
                <linearGradient id="dh_bg" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#f0f4ff" stop-opacity="0"/><stop offset="28%" stop-color="#edf2ff" stop-opacity="0.55"/><stop offset="100%" stop-color="#e0eaff" stop-opacity="1"/></linearGradient>
                <linearGradient id="dh_ind" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#818cf8"/><stop offset="100%" stop-color="#4f46e5"/></linearGradient>
                <linearGradient id="dh_grn" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#34d399"/><stop offset="100%" stop-color="#059669"/></linearGradient>
                <linearGradient id="dh_amb" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#fbbf24"/><stop offset="100%" stop-color="#d97706"/></linearGradient>
                <linearGradient id="dh_scr" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#eff6ff"/><stop offset="100%" stop-color="#dbeafe"/></linearGradient>
                <filter id="dh_sh" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="rgba(79,70,229,0.18)"/></filter>
                <filter id="dh_shSm" x="-20%" y="-20%" width="140%" height="140%"><feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="rgba(79,70,229,0.14)"/></filter>
                <filter id="dh_shLg" x="-10%" y="-10%" width="120%" height="130%"><feDropShadow dx="2" dy="8" stdDeviation="10" flood-color="rgba(99,102,241,0.18)"/></filter>
            </defs>
            <!-- Background wash -->
            <rect width="680" height="168" fill="url(#dh_bg)"/>
            <!-- Deco dots -->
            <circle cx="646" cy="16" r="5" fill="rgba(99,102,241,0.25)"/>
            <circle cx="662" cy="8" r="3.5" fill="rgba(139,92,246,0.2)"/>
            <circle cx="633" cy="25" r="3" fill="rgba(99,102,241,0.15)"/>
            <!-- Left small node -->
            <circle cx="172" cy="84" r="16" fill="white" filter="url(#dh_shSm)"/>
            <circle cx="172" cy="84" r="11" fill="#eef2ff"/>
            <circle cx="172" cy="84" r="6" fill="#a5b4fc"/>
            <!-- Connection lines from hub -->
            <line x1="188" y1="84" x2="266" y2="84" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.65"/>
            <line x1="346" y1="58" x2="374" y2="40" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.75"/>
            <line x1="350" y1="84" x2="404" y2="84" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.75"/>
            <line x1="346" y1="110" x2="374" y2="130" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.65"/>
            <line x1="500" y1="84" x2="540" y2="84" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.65"/>
            <!-- ═══ HUB circle (control panel) ═══ -->
            <circle cx="308" cy="84" r="42" fill="white" filter="url(#dh_sh)"/>
            <circle cx="308" cy="84" r="34" fill="#f0f4ff"/>
            <!-- Slider 1 -->
            <line x1="290" y1="73" x2="326" y2="73" stroke="#c7d2fe" stroke-width="3" stroke-linecap="round"/>
            <circle cx="304" cy="73" r="6.5" fill="#4f46e5" stroke="white" stroke-width="2.5"/>
            <!-- Slider 2 -->
            <line x1="290" y1="84" x2="326" y2="84" stroke="#c7d2fe" stroke-width="3" stroke-linecap="round"/>
            <circle cx="316" cy="84" r="6.5" fill="#4f46e5" stroke="white" stroke-width="2.5"/>
            <!-- Slider 3 -->
            <line x1="290" y1="95" x2="326" y2="95" stroke="#c7d2fe" stroke-width="3" stroke-linecap="round"/>
            <circle cx="298" cy="95" r="6.5" fill="#22c55e" stroke="white" stroke-width="2.5"/>
            <!-- ═══ FLOATING ICON CARDS ═══ -->
            <!-- Top icon: AI Hub / sparkle (indigo) -->
            <rect x="228" y="14" width="50" height="50" rx="14" fill="url(#dh_ind)" filter="url(#dh_sh)"/>
            <path d="M253 24 L256 34 L266 37 L256 40 L253 50 L250 40 L240 37 L250 34 Z" fill="rgba(255,255,255,0.88)"/>
            <!-- Bottom icon: Shield check (green) -->
            <rect x="228" y="106" width="50" height="50" rx="14" fill="url(#dh_grn)" filter="url(#dh_sh)"/>
            <path d="M253 116 L263 120 L263 132 Q257 138 253 140 Q249 138 243 132 L243 120 Z" fill="rgba(255,255,255,0.2)"/>
            <path d="M247 130 L251 134 L260 123" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <!-- Right icon: Gear / settings (amber) -->
            <rect x="374" y="58" width="44" height="52" rx="12" fill="url(#dh_amb)" filter="url(#dh_shSm)"/>
            <circle cx="396" cy="84" r="10" stroke="rgba(255,255,255,0.7)" stroke-width="2.5" fill="none"/>
            <circle cx="396" cy="84" r="4" fill="rgba(255,255,255,0.85)"/>
            <circle cx="396" cy="74" r="2.5" fill="rgba(255,255,255,0.6)"/>
            <circle cx="396" cy="94" r="2.5" fill="rgba(255,255,255,0.6)"/>
            <circle cx="386" cy="79" r="2.5" fill="rgba(255,255,255,0.6)"/>
            <circle cx="406" cy="79" r="2.5" fill="rgba(255,255,255,0.6)"/>
            <circle cx="386" cy="89" r="2.5" fill="rgba(255,255,255,0.6)"/>
            <circle cx="406" cy="89" r="2.5" fill="rgba(255,255,255,0.6)"/>
            <!-- ═══ RIGHT SCREEN (laptop) ═══ -->
            <!-- Laptop base -->
            <path d="M548 145 L658 145 L654 160 L552 160 Z" fill="url(#dh_scr)" filter="url(#dh_shLg)"/>
            <rect x="588" y="156" width="28" height="6" rx="3" fill="#bfdbfe"/>
            <!-- Screen frame -->
            <rect x="548" y="24" width="110" height="124" rx="10" fill="white" filter="url(#dh_shLg)"/>
            <rect x="548" y="24" width="110" height="124" rx="10" stroke="#c7d8f2" stroke-width="1.5" fill="none"/>
            <!-- Bezel -->
            <rect x="554" y="30" width="98" height="112" rx="7" fill="#f0f4ff"/>
            <!-- Screen header -->
            <rect x="558" y="34" width="86" height="16" rx="5" fill="#4f46e5"/>
            <circle cx="568" cy="42" r="5" fill="rgba(255,255,255,0.22)"/>
            <rect x="578" y="38" width="36" height="4" rx="2" fill="rgba(255,255,255,0.6)"/>
            <rect x="578" y="46" width="22" height="3" rx="1.5" fill="rgba(255,255,255,0.35)"/>
            <!-- Mini card grid 2×3 on screen -->
            <rect x="558" y="56" width="40" height="20" rx="5" fill="white"/><rect x="558" y="56" width="40" height="3.5" rx="2" fill="#22c55e"/><rect x="562" y="64" width="18" height="3" rx="1.5" fill="#e2e8f0"/><rect x="562" y="69" width="12" height="2.5" rx="1.25" fill="#f1f5f9"/>
            <rect x="604" y="56" width="40" height="20" rx="5" fill="white"/><rect x="604" y="56" width="40" height="3.5" rx="2" fill="#4f46e5"/><rect x="608" y="64" width="18" height="3" rx="1.5" fill="#e2e8f0"/><rect x="608" y="69" width="12" height="2.5" rx="1.25" fill="#f1f5f9"/>
            <rect x="558" y="82" width="40" height="20" rx="5" fill="white"/><rect x="558" y="82" width="40" height="3.5" rx="2" fill="#22c55e"/><rect x="562" y="90" width="18" height="3" rx="1.5" fill="#e2e8f0"/><rect x="562" y="95" width="12" height="2.5" rx="1.25" fill="#f1f5f9"/>
            <rect x="604" y="82" width="40" height="20" rx="5" fill="white"/><rect x="604" y="82" width="40" height="3.5" rx="2" fill="#f59e0b"/><rect x="608" y="90" width="18" height="3" rx="1.5" fill="#e2e8f0"/><rect x="608" y="95" width="12" height="2.5" rx="1.25" fill="#f1f5f9"/>
            <rect x="558" y="108" width="40" height="20" rx="5" fill="white"/><rect x="558" y="108" width="40" height="3.5" rx="2" fill="#e2e8f0"/><rect x="562" y="116" width="18" height="3" rx="1.5" fill="#e2e8f0"/><rect x="562" y="121" width="12" height="2.5" rx="1.25" fill="#f1f5f9"/>
            <rect x="604" y="108" width="40" height="20" rx="5" fill="white"/><rect x="604" y="108" width="40" height="3.5" rx="2" fill="#4f46e5"/><rect x="608" y="116" width="18" height="3" rx="1.5" fill="#e2e8f0"/><rect x="608" y="121" width="12" height="2.5" rx="1.25" fill="#f1f5f9"/>
            <!-- Status bar -->
            <rect x="558" y="132" width="86" height="8" rx="3" fill="#f8fafc"/>
            <circle cx="566" cy="136" r="3" fill="#22c55e"/>
            <rect x="573" y="134" width="26" height="3" rx="1.5" fill="#e2e8f0"/>
            <rect x="620" y="134" width="18" height="3" rx="1.5" fill="#bfdbfe"/>
            <!-- Paper plane deco -->
            <g transform="translate(646,136) rotate(-18)" opacity="0.75">
                <path d="M0 9 L26 0 L8 18 Z" fill="#60a5fa"/>
                <path d="M8 18 L6 11 L26 0 Z" fill="#93c5fd"/>
            </g>
            <!-- Plant bottom-right -->
            <rect x="660" y="150" width="6" height="16" rx="3" fill="#4ade80" opacity="0.9"/>
            <ellipse cx="655" cy="147" rx="12" ry="7" fill="#22c55e" opacity="0.92" transform="rotate(-30 655 147)"/>
            <ellipse cx="668" cy="144" rx="10" ry="6" fill="#4ade80" opacity="0.85" transform="rotate(22 668 144)"/>
            <ellipse cx="661" cy="136" rx="8" ry="4.5" fill="#86efac" opacity="0.9" transform="rotate(-8 661 136)"/>
        </svg>
    </div>
</div>

<!-- Module grid -->
<div class="whp-dash-grid">

    <!-- 1. Kênh liên hệ -->
    <?php $card_cls = ($contact_on && $contact_count > 0) ? 'active' : ($contact_count > 0 ? 'warn' : 'gray'); ?>
    <div class="whp-dash-card whp-dash-card--<?php echo $card_cls; ?>">
        <div class="whp-dash-card-head">
            <div class="whp-dash-card-icon whp-dash-card-icon--<?php echo $contact_count > 0 ? 'green' : 'gray'; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.24h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.82A16 16 0 0 0 12 13.06a16 16 0 0 0 4.24.94l1.16-1.16a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            </div>
            <span class="whp-dash-badge whp-dash-badge--<?php echo ($contact_on && $contact_count > 0) ? 'on' : (($contact_count > 0) ? 'warn' : 'off'); ?>">
                <?php echo ($contact_on && $contact_count > 0) ? esc_html__( 'Đang bật', 'whp' ) : (($contact_count > 0) ? esc_html__( 'Chưa bật', 'whp' ) : esc_html__( 'Chưa cấu hình', 'whp' )); ?>
            </span>
        </div>
        <div class="whp-dash-card-name"><?php esc_html_e( 'Kênh liên hệ', 'whp' ); ?></div>
        <div class="whp-dash-card-meta">
            <?php if ( $contact_count > 0 ) : ?>
                <strong><?php echo $contact_count; ?> <?php esc_html_e( 'kênh', 'whp' ); ?></strong> <?php esc_html_e( 'đã cấu hình:', 'whp' ); ?> <?php echo esc_html( implode( ', ', $channels ) ); ?>
            <?php else : ?>
                <?php esc_html_e( 'Chưa có kênh nào. Thêm Zalo, Messenger, Hotline…', 'whp' ); ?>
            <?php endif; ?>
        </div>
        <div class="whp-dash-card-footer">
            <a class="whp-dash-btn" href="<?php echo esc_url( $url('mb-wphelper-contact') ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                <?php esc_html_e( 'Quản lý kênh liên hệ', 'whp' ); ?>
            </a>
        </div>
    </div>

    <!-- 2. Pop-up -->
    <?php $card_cls2 = $popup_active ? 'active' : 'gray'; ?>
    <div class="whp-dash-card whp-dash-card--<?php echo $card_cls2; ?>">
        <div class="whp-dash-card-head">
            <div class="whp-dash-card-icon whp-dash-card-icon--<?php echo $popup_active ? 'blue' : 'gray'; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/></svg>
            </div>
            <span class="whp-dash-badge whp-dash-badge--<?php echo $popup_active ? 'on' : 'off'; ?>">
                <?php echo $popup_active ? esc_html__( 'Đang bật', 'whp' ) : esc_html__( 'Đang tắt', 'whp' ); ?>
            </span>
        </div>
        <div class="whp-dash-card-name">Pop-up</div>
        <div class="whp-dash-card-meta">
            <?php if ( $popup_active ) : ?>
                <?php esc_html_e( 'Loại:', 'whp' ); ?> <strong><?php echo esc_html( $popup_type_label ); ?></strong><br>
                <?php esc_html_e( 'Tiêu đề:', 'whp' ); ?> <strong><?php echo esc_html( $popup_title ); ?></strong>
            <?php else : ?>
                <?php esc_html_e( 'Popup quảng cáo, thu thập email hoặc link mạng xã hội.', 'whp' ); ?>
            <?php endif; ?>
        </div>
        <div class="whp-dash-card-footer">
            <a class="whp-dash-btn" href="<?php echo esc_url( $url('mb-wphelper-pop-up') ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                <?php esc_html_e( 'Cấu hình Pop-up', 'whp' ); ?>
            </a>
        </div>
    </div>

    <!-- 3. Email & SMTP -->
    <?php $card_cls3 = $smtp_ok ? 'active' : ($smtp_active ? 'warn' : 'gray'); ?>
    <div class="whp-dash-card whp-dash-card--<?php echo $card_cls3; ?>">
        <div class="whp-dash-card-head">
            <div class="whp-dash-card-icon whp-dash-card-icon--<?php echo $smtp_ok ? 'teal' : 'gray'; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <span class="whp-dash-badge whp-dash-badge--<?php echo $smtp_ok ? 'on' : ($smtp_active ? 'warn' : 'off'); ?>">
                <?php echo $smtp_ok ? esc_html__( 'Đã kết nối', 'whp' ) : ($smtp_active ? esc_html__( 'Thiếu host', 'whp' ) : esc_html__( 'Chưa cấu hình', 'whp' )); ?>
            </span>
        </div>
        <div class="whp-dash-card-name">Email & SMTP</div>
        <div class="whp-dash-card-meta">
            <?php if ( $smtp_ok ) : ?>
                Server: <strong><?php echo esc_html( $smtp_host ); ?></strong><br>
                <?php if ( !empty($smtp_email) ) : ?><?php esc_html_e( 'Gửi từ:', 'whp' ); ?> <strong><?php echo esc_html( $smtp_email ); ?></strong><?php endif; ?>
            <?php else : ?>
                <?php esc_html_e( 'Cấu hình SMTP để email liên hệ, thông báo đơn hàng không vào Spam.', 'whp' ); ?>
            <?php endif; ?>
        </div>
        <div class="whp-dash-card-footer">
            <a class="whp-dash-btn" href="<?php echo esc_url( $url('mb-wphelper-smtp') ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                <?php esc_html_e( 'Cấu hình SMTP', 'whp' ); ?>
            </a>
        </div>
    </div>

    <!-- 4. AI Hub -->
    <?php $card_cls4 = $ai_ok ? 'active' : 'gray'; ?>
    <div class="whp-dash-card whp-dash-card--<?php echo $card_cls4; ?>">
        <div class="whp-dash-card-head">
            <div class="whp-dash-card-icon whp-dash-card-icon--<?php echo $ai_ok ? 'purple' : 'gray'; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/></svg>
            </div>
            <span class="whp-dash-badge whp-dash-badge--<?php echo $ai_ok ? 'on' : 'off'; ?>">
                <?php echo $ai_ok ? esc_html__( 'Đã kết nối', 'whp' ) : esc_html__( 'Chưa kết nối', 'whp' ); ?>
            </span>
        </div>
        <div class="whp-dash-card-name">AI Hub</div>
        <div class="whp-dash-card-meta">
            <?php if ( $ai_ok ) : ?>
                <?php esc_html_e( 'Provider:', 'whp' ); ?> <strong><?php echo esc_html( $ai_connected_name ); ?></strong><br>
                <?php esc_html_e( 'Tính năng: Viết bài, SEO tối ưu, phân tích bảo mật', 'whp' ); ?>
            <?php else : ?>
                <?php esc_html_e( 'Kết nối Google Gemini, OpenAI hoặc Anthropic Claude để viết bài tự động.', 'whp' ); ?>
            <?php endif; ?>
            <br>
            <?php if ( $maint_on ) : ?>
                <span class="whp-dash-badge whp-dash-badge--maint" style="display:inline-flex;align-items:center;gap:4px;margin-top:6px;">&#9888; <?php esc_html_e( 'Bảo trì đang BẬT', 'whp' ); ?></span>
            <?php else : ?>
                <span style="display:inline-block;margin-top:6px;"><?php esc_html_e( 'Bảo trì:', 'whp' ); ?> <strong><?php esc_html_e( 'Tắt', 'whp' ); ?></strong></span>
            <?php endif; ?>
        </div>
        <div class="whp-dash-card-footer">
            <a class="whp-dash-btn" href="<?php echo esc_url( $url('mb-wphelper-ai') ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                <?php esc_html_e( 'Vào AI Hub', 'whp' ); ?>
            </a>
        </div>
    </div>

    <!-- 5. Bảo vệ & Tối ưu -->
    <?php
    $total_protect_on    = $sec1_on + $sec2_on + $sec3_on;
    $total_protect_total = $sec1_total + $sec2_total + $sec3_total;
    $protect_level    = ($total_protect_on >= 10) ? 'active' : (($total_protect_on >= 4) ? 'warn' : 'gray');
    $protect_badge    = ($total_protect_on >= 10) ? 'on'     : (($total_protect_on >= 4) ? 'warn' : 'off');
    $protect_icon_cls = ($total_protect_on >= 10) ? 'red'    : (($total_protect_on >= 4) ? 'amber' : 'gray');
    ?>
    <div class="whp-dash-card whp-dash-card--<?php echo $protect_level; ?>">
        <div class="whp-dash-card-head">
            <div class="whp-dash-card-icon whp-dash-card-icon--<?php echo $protect_icon_cls; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <span class="whp-dash-badge whp-dash-badge--<?php echo $protect_badge; ?>">
                <?php echo $total_protect_on; ?>/<?php echo $total_protect_total; ?> <?php esc_html_e( 'bật', 'whp' ); ?>
            </span>
        </div>
        <div class="whp-dash-card-name"><?php esc_html_e( 'Bảo vệ & Tối ưu', 'whp' ); ?></div>
        <div class="whp-dash-card-meta">
            <?php esc_html_e( 'Bảo vệ Website:', 'whp' ); ?> <strong><?php echo $sec1_on; ?>/<?php echo $sec1_total; ?></strong> <?php esc_html_e( 'bật', 'whp' ); ?><br>
            <?php esc_html_e( 'Tối ưu Website:', 'whp' ); ?> <strong><?php echo $sec2_on; ?>/<?php echo $sec2_total; ?></strong> <?php esc_html_e( 'bật', 'whp' ); ?><br>
            <?php esc_html_e( 'Quản trị hệ thống:', 'whp' ); ?> <strong><?php echo $sec3_on; ?>/<?php echo $sec3_total; ?></strong> <?php esc_html_e( 'bật', 'whp' ); ?>
        </div>
        <div class="whp-dash-card-footer">
            <a class="whp-dash-btn" href="<?php echo esc_url( $url('mb-wphelper-security') ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                <?php esc_html_e( 'Quản lý bảo vệ', 'whp' ); ?>
            </a>
        </div>
    </div>

    <!-- 6. Cửa hàng nâng cao -->
    <?php $card_cls6 = $woo_active ? 'active' : 'gray'; ?>
    <div class="whp-dash-card whp-dash-card--<?php echo $card_cls6; ?>">
        <div class="whp-dash-card-head">
            <div class="whp-dash-card-icon whp-dash-card-icon--<?php echo $woo_active ? 'amber' : 'gray'; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <span class="whp-dash-badge whp-dash-badge--<?php echo $woo_active ? 'on' : 'off'; ?>">
                <?php echo $woo_active ? 'WooCommerce ' . esc_html($woo_ver) : esc_html__( 'Chưa cài', 'whp' ); ?>
            </span>
        </div>
        <div class="whp-dash-card-name"><?php esc_html_e( 'Cửa hàng nâng cao', 'whp' ); ?></div>
        <div class="whp-dash-card-meta">
            <?php if ( $woo_active ) : ?>
                <?php esc_html_e( 'WooCommerce đang hoạt động.', 'whp' ); ?><br>
                <?php esc_html_e( 'Nâng cao: thanh toán QR, ví điểm thưởng, CTA đặt hàng.', 'whp' ); ?>
            <?php else : ?>
                <?php esc_html_e( 'Cài WooCommerce để mở khóa tính năng thương mại điện tử nâng cao.', 'whp' ); ?>
            <?php endif; ?>
        </div>
        <div class="whp-dash-card-footer">
            <a class="whp-dash-btn <?php echo !$woo_active ? 'whp-dash-btn--gray' : ''; ?>" href="<?php echo esc_url( $url('mb-wphelper-woocommerce-advance') ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                <?php echo $woo_active ? esc_html__( 'Cấu hình cửa hàng', 'whp' ) : esc_html__( 'Xem tính năng', 'whp' ); ?>
            </a>
        </div>
    </div>

    <!-- 7. Header & Footer Code -->
    <?php $card_cls7 = $code_count > 0 ? 'active' : 'gray'; ?>
    <div class="whp-dash-card whp-dash-card--<?php echo $card_cls7; ?>">
        <div class="whp-dash-card-head">
            <div class="whp-dash-card-icon whp-dash-card-icon--<?php echo $code_count > 0 ? 'teal' : 'gray'; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
            </div>
            <span class="whp-dash-badge whp-dash-badge--<?php echo $code_count > 0 ? 'on' : 'off'; ?>">
                <?php echo $code_count > 0 ? $code_count . ' ' . esc_html__( 'vùng đang dùng', 'whp' ) : esc_html__( 'Trống', 'whp' ); ?>
            </span>
        </div>
        <div class="whp-dash-card-name">Header & Footer Code</div>
        <div class="whp-dash-card-meta">
            <?php if ( $code_count > 0 ) : ?>
                <?php esc_html_e( 'Có code trong:', 'whp' ); ?> <strong><?php echo esc_html( implode( ', ', $code_zones ) ); ?></strong><br>
                <span style="font-size:12px;color:#94a3b8;"><?php esc_html_e( 'Script tracking, pixel, CSS tùy chỉnh…', 'whp' ); ?></span>
            <?php else : ?>
                <?php esc_html_e( 'Chèn script tracking (Google Analytics, Facebook Pixel), CSS tùy chỉnh vào website.', 'whp' ); ?>
            <?php endif; ?>
        </div>
        <div class="whp-dash-card-footer">
            <a class="whp-dash-btn" href="<?php echo esc_url( $url('mb-wphelper-code') ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
                <?php esc_html_e( 'Chèn code', 'whp' ); ?>
            </a>
        </div>
    </div>

</div><!-- /.whp-dash-grid -->
</div><!-- /.whp-dash -->

<?php whp_get_shared( 'footer' ); ?>
