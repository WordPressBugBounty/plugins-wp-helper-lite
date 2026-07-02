<?php
defined('ABSPATH') || exit;

function wpaap_aipay_config_hero( $is_active = false ) { ?>
<style>
.wpaap-cfg-hero {
    background-color: #fff1f2;
    background-image:
        linear-gradient(110deg, rgba(255,255,255,.97) 0%, rgba(255,241,242,.94) 40%, rgba(255,228,230,.9) 100%),
        radial-gradient(circle, #fda4af 1.5px, transparent 1.5px);
    background-size: auto, 22px 22px;
    border-radius: 18px; border: 1px solid #fecdd3;
    box-shadow: 0 4px 20px rgba(225,29,72,0.10);
    padding: 24px 28px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 20px;
    overflow: hidden; position: relative; min-height: 88px;
}
.wpaap-cfg-hero-icon {
    width: 50px; height: 50px; border-radius: 14px;
    background: linear-gradient(135deg, #e11d48, #be123c);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(225,29,72,0.35); flex-shrink: 0;
}
.wpaap-cfg-hero-text h2 { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #881337; }
.wpaap-cfg-hero-text p  { margin: 0; font-size: 13px; color: #9f1239; line-height: 1.6; }
.wpaap-cfg-hero-deco {
    position: absolute; right: 0; top: 0; bottom: 0; width: 380px;
    pointer-events: none; overflow: hidden;
}
.wpaap-cfg-hero-deco svg { display: block; width: 100%; height: 100%; overflow: hidden; }
</style>
<div class="wpaap-cfg-hero">
    <div style="display:flex;align-items:center;gap:16px;">
        <div class="wpaap-cfg-hero-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </div>
        <div class="wpaap-cfg-hero-text">
            <h2><?php esc_html_e('Cấu hình chung', 'whp'); ?></h2>
            <p><?php esc_html_e('Bật/tắt các tính năng AI Thanh Toán và cấu hình API Keys để tích hợp mô hình AI.', 'whp'); ?></p>
        </div>
    </div>

    <!-- Decorative illustration (absolute, no text elements to avoid overflow) -->
    <div class="wpaap-cfg-hero-deco" aria-hidden="true">
        <svg viewBox="0 0 380 88" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMaxYMid meet">
            <defs>
                <linearGradient id="cfgFade" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#fff1f2" stop-opacity="1"/>
                    <stop offset="30%" stop-color="#fff1f2" stop-opacity="0"/>
                </linearGradient>
                <radialGradient id="cfgGlow" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#e11d48" stop-opacity="0.15"/>
                    <stop offset="100%" stop-color="#e11d48" stop-opacity="0"/>
                </radialGradient>
            </defs>

            <!-- Glow behind rings -->
            <circle cx="310" cy="44" r="70" fill="url(#cfgGlow)"/>

            <!-- Concentric rings -->
            <circle cx="310" cy="44" r="72" fill="none" stroke="#fecdd3" stroke-width="1" opacity="0.45"/>
            <circle cx="310" cy="44" r="54" fill="none" stroke="#fda4af" stroke-width="1.2" opacity="0.4"/>
            <circle cx="310" cy="44" r="38" fill="none" stroke="#fb7185" stroke-width="1" opacity="0.3"/>
            <circle cx="310" cy="44" r="24" fill="#ffe4e6" opacity="0.55"/>

            <!-- Center check badge -->
            <circle cx="310" cy="44" r="16" fill="#e11d48"/>
            <polyline points="303,44 307.5,48.5 317,39" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>

            <!-- 3 feature nodes on left -->
            <circle cx="108" cy="18" r="9" fill="#fff" stroke="#fda4af" stroke-width="1.5"/>
            <circle cx="88" cy="44" r="9" fill="#fff" stroke="#fb7185" stroke-width="1.5"/>
            <circle cx="108" cy="70" r="9" fill="#fff" stroke="#fda4af" stroke-width="1.5"/>

            <!-- OCR icon: doc lines inside node 1 -->
            <line x1="104" y1="15" x2="112" y2="15" stroke="#e11d48" stroke-width="1.3"/>
            <line x1="104" y1="18" x2="112" y2="18" stroke="#e11d48" stroke-width="1.3"/>
            <line x1="104" y1="21" x2="110" y2="21" stroke="#e11d48" stroke-width="1.3"/>

            <!-- Shield icon inside node 2 -->
            <path d="M85 41 L88 39.5 L91 41 L91 45.5 C91 47.5 88 49 88 49 C88 49 85 47.5 85 45.5 Z" fill="none" stroke="#e11d48" stroke-width="1.3" stroke-linejoin="round"/>

            <!-- Star icon inside node 3 -->
            <polygon points="108,65.5 109.4,69.2 113.3,69.2 110.2,71.5 111.4,75.2 108,72.9 104.6,75.2 105.8,71.5 102.7,69.2 106.6,69.2" fill="none" stroke="#0ea5e9" stroke-width="1.1" stroke-linejoin="round"/>

            <!-- Dashed connector lines: nodes → center -->
            <line x1="117" y1="18" x2="262" y2="40" stroke="#fda4af" stroke-width="1" stroke-dasharray="5 3" opacity="0.55"/>
            <line x1="97"  y1="44" x2="262" y2="44" stroke="#fda4af" stroke-width="1" stroke-dasharray="5 3" opacity="0.55"/>
            <line x1="117" y1="70" x2="262" y2="48" stroke="#fda4af" stroke-width="1" stroke-dasharray="5 3" opacity="0.55"/>

            <!-- Mid-point relay nodes -->
            <circle cx="190" cy="29" r="3.5" fill="#fff" stroke="#fecdd3" stroke-width="1.2"/>
            <circle cx="180" cy="44" r="3.5" fill="#fff" stroke="#fecdd3" stroke-width="1.2"/>
            <circle cx="190" cy="59" r="3.5" fill="#fff" stroke="#fecdd3" stroke-width="1.2"/>

            <!-- Scattered accent dots -->
            <circle cx="48"  cy="14" r="3"   fill="#fda4af" opacity="0.4"/>
            <circle cx="60"  cy="76" r="2.5" fill="#fb7185" opacity="0.35"/>
            <circle cx="148" cy="8"  r="2"   fill="#fecdd3" opacity="0.5"/>
            <circle cx="355" cy="12" r="4"   fill="#fecdd3" opacity="0.35"/>
            <circle cx="362" cy="78" r="2.5" fill="#fda4af" opacity="0.3"/>

            <!-- Left-edge fade to blend into hero background -->
            <rect x="0" y="0" width="90" height="88" fill="url(#cfgFade)"/>
        </svg>
    </div>
</div>
<?php }

function wpaap_aipay_config_layout()
{
    $whp_aipay_enable         = whp_get_setting('whp_aipay_enable');
    $whp_aipay_ocr_enable     = whp_get_setting('whp_aipay_ocr_enable');
    $whp_aipay_fraud_enable   = whp_get_setting('whp_aipay_fraud_enable');
    $whp_aipay_copilot_enable = whp_get_setting('whp_aipay_copilot_enable');
    $whp_aipay_openai_key     = whp_get_setting('whp_aipay_openai_key');
    $whp_aipay_gemini_key     = whp_get_setting('whp_aipay_gemini_key');

    $any_ai_on = $whp_aipay_ocr_enable || $whp_aipay_fraud_enable || $whp_aipay_copilot_enable;

    $isSubmit = 0;
    if (isset($_POST['submit'])) {
        if (!wp_verify_nonce($_POST['_token'], '_token')) {
            wp_die('Security check failed.');
        }

        $fields = [
            'whp_aipay_ocr_enable',
            'whp_aipay_fraud_enable',
            'whp_aipay_copilot_enable',
        ];
        $option = get_option('whp_setting', []);
        $params = $option ?: [];

        foreach ($fields as $f) {
            $params[$f] = isset($_POST[$f]) ? '1' : '0';
        }

        // Enable toggle is synced from header via hidden input (Kiểu 1 pattern)
        $params['whp_aipay_enable'] = (($_POST['whp_aipay_enable'] ?? '0') === '1') ? '1' : '0';

        $params['whp_aipay_openai_key'] = isset($_POST['whp_aipay_openai_key'])
            ? sanitize_text_field(wp_unslash($_POST['whp_aipay_openai_key']))
            : ($params['whp_aipay_openai_key'] ?? '');

        $params['whp_aipay_gemini_key'] = isset($_POST['whp_aipay_gemini_key'])
            ? sanitize_text_field(wp_unslash($_POST['whp_aipay_gemini_key']))
            : ($params['whp_aipay_gemini_key'] ?? '');

        update_option('whp_setting', $params);
        $isSubmit = 1;

        $whp_aipay_enable         = $params['whp_aipay_enable'];
        $whp_aipay_ocr_enable     = $params['whp_aipay_ocr_enable'];
        $whp_aipay_fraud_enable   = $params['whp_aipay_fraud_enable'];
        $whp_aipay_copilot_enable = $params['whp_aipay_copilot_enable'];
        $whp_aipay_openai_key     = $params['whp_aipay_openai_key'];
        $whp_aipay_gemini_key     = $params['whp_aipay_gemini_key'];
        $any_ai_on = $whp_aipay_ocr_enable || $whp_aipay_fraud_enable || $whp_aipay_copilot_enable;
    }

    // WooCommerce stats
    $s_pending = 0; $s_verified = 0; $s_risk = 0; $s_total_count = 0;
    $y_verified = 0; $y_risk = 0; $y_total_count = 0;
    if (function_exists('wc_get_orders')) {
        $s_pending = count(wc_get_orders(['status'=>['on-hold'],'meta_query'=>[['key'=>'_whp_transfer_confirmed_at','compare'=>'EXISTS']],'limit'=>-1,'return'=>'ids']));
        $today_orders = wc_get_orders(['date_created'=>date('Y-m-d'),'meta_query'=>[['key'=>'_whp_transfer_confirmed_at','compare'=>'EXISTS']],'limit'=>500,'return'=>'objects']);
        $s_total_count = count($today_orders);
        foreach ($today_orders as $ord) {
            $ai = $ord->get_meta('_whp_ai_verify_result');
            if (is_array($ai) && !empty($ai['verdict'])) {
                if ($ai['verdict']==='valid') $s_verified++;
                elseif (in_array($ai['verdict'],['suspicious','invalid'])) $s_risk++;
            }
        }
        $yesterday_orders = wc_get_orders(['date_created'=>date('Y-m-d',strtotime('-1 day')),'meta_query'=>[['key'=>'_whp_transfer_confirmed_at','compare'=>'EXISTS']],'limit'=>500,'return'=>'objects']);
        $y_total_count = count($yesterday_orders);
        foreach ($yesterday_orders as $ord) {
            $ai = $ord->get_meta('_whp_ai_verify_result');
            if (is_array($ai) && !empty($ai['verdict'])) {
                if ($ai['verdict']==='valid') $y_verified++;
                elseif (in_array($ai['verdict'],['suspicious','invalid'])) $y_risk++;
            }
        }
    }
    $stat_trend = static function($now, $prev) {
        if ($prev <= 0) return null;
        return ['pct'=>round(abs($now-$prev)/$prev*100),'dir'=>($now>=$prev)?'up':'down'];
    };

    // Provider data — đồng bộ model từ trang Kết nối AI
    $conn_url = admin_url('admin.php?page=mb-wphelper-ai&subtab=connection');
    $logs_url = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=logs');
    $cfg_url  = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=config');
    $aipay_model_labels = [
        'google' => [
            'auto'                  => __('Tự động', 'whp'),
            'gemini-2.5-flash'      => 'Gemini 2.5 Flash',
            'gemini-2.5-pro'        => 'Gemini 2.5 Pro',
            'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash-Lite',
            'gemini-2.0-flash'      => 'Gemini 2.0 Flash',
            'gemini-1.5-flash'      => 'Gemini 1.5 Flash',
            'gemini-1.5-pro'        => 'Gemini 1.5 Pro',
        ],
        'anthropic' => [
            'claude-opus-4-8'            => 'Claude Opus 4.8',
            'claude-sonnet-4-6'          => 'Claude Sonnet 4.6',
            'claude-haiku-4-5-20251001'  => 'Claude Haiku 4.5',
            'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
            'claude-3-5-haiku-20241022'  => 'Claude 3.5 Haiku',
            'claude-3-5-sonnet'          => 'Claude 3.5 Sonnet',
            'claude-3-opus'              => 'Claude 3 Opus',
            'claude-3-haiku'             => 'Claude 3 Haiku',
        ],
        'openai' => [
            'gpt-4o'        => 'GPT-4o',
            'gpt-4o-mini'   => 'GPT-4o Mini',
            'gpt-4-turbo'   => 'GPT-4 Turbo',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
        ],
    ];
    $aipay_default_models = ['google' => 'gemini-2.5-flash', 'anthropic' => 'claude-sonnet-4-6', 'openai' => 'gpt-4o'];
    $providers = [
        'google'    => ['label'=>'Google Gemini',    'logo'=>'<svg viewBox="0 0 28 28" width="26" height="26"><path d="M14 28C14 26.0633 13.6267 24.2433 12.88 22.54C12.1567 20.8367 11.165 19.355 9.905 18.095C8.645 16.835 7.16333 15.8433 5.46 15.12C3.75667 14.3733 1.93667 14 0 14C1.93667 14 3.75667 13.6383 5.46 12.915C7.16333 12.1683 8.645 11.165 9.905 9.905C11.165 8.645 12.1567 7.16333 12.88 5.46C13.6267 3.75667 14 1.93667 14 0C14 1.93667 14.3617 3.75667 15.085 5.46C15.8317 7.16333 16.835 8.645 18.095 9.905C19.355 11.165 20.8367 12.1683 22.54 12.915C24.2433 13.6383 26.0633 14 28 14C26.0633 14 24.2433 14.3733 22.54 15.12C20.8367 15.8433 19.355 16.835 18.095 18.095C16.835 19.355 15.8317 20.8367 15.085 22.54C14.3617 24.2433 14 26.0633 14 28Z" fill="url(#gemCfg)"/><defs><radialGradient id="gemCfg" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(2.77876 11.3795) rotate(18.6832) scale(29.8025 238.737)"><stop offset="0.0671246" stop-color="#9168C0"/><stop offset="0.342551" stop-color="#5684D1"/><stop offset="0.672076" stop-color="#1BA1E3"/></radialGradient></defs></svg>'],
        'anthropic' => ['label'=>'Anthropic Claude', 'logo'=>'<svg viewBox="0 0 24 24" width="24" height="24" fill="#D97757"><path d="m4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z"/></svg>'],
        'openai'    => ['label'=>'OpenAI GPT',       'logo'=>'<svg viewBox="0 0 24 24" width="24" height="24" fill="#000"><path d="M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z"/></svg>'],
    ];
    foreach ($providers as $k => $_) {
        $model_id = get_option( "wpaap_{$k}_model", $aipay_default_models[$k] ?? '' );
        $providers[$k]['model']     = $aipay_model_labels[$k][$model_id] ?? $model_id;
        $providers[$k]['connected'] = function_exists('wpaap_is_provider_connected') && wpaap_is_provider_connected($k);
    }
    ?>
<style>
/* ── Layout ────────────────────────────────────────────────────────── */
.wpaap-cfg-wrap { max-width: none; margin: 0; padding: 0; }
.wpaap-cfg-grid { display: grid; grid-template-columns: 1fr 320px; gap: 24px; align-items: start; }
.wpaap-cfg-main { display: flex; flex-direction: column; gap: 0; }

/* ── Notify ─────────────────────────────────────────────────────────── */
.wpaap-cfg-notify {
    background: #ecfdf5; border: 1px solid #6ee7b7;
    color: #065f46; border-radius: 10px;
    padding: 12px 18px; font-size: 13.5px; font-weight: 600;
    margin-bottom: 20px;
    display: flex; align-items: center; gap: 10px;
}

/* ── Section cards ──────────────────────────────────────────────────── */
.wpaap-cfg-section {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 20px;
    overflow: hidden;
}
.wpaap-cfg-section-head {
    padding: 16px 20px 14px;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: 12px;
}
.wpaap-cfg-section-head-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wpaap-cfg-section-head h3 { margin: 0 0 2px; font-size: 15px; font-weight: 700; color: #0f172a; }
.wpaap-cfg-section-head p  { margin: 0; font-size: 12.5px; color: #64748b; line-height: 1.5; }
.wpaap-cfg-section-body { padding: 4px 20px 8px; }

/* ── Feature rows ───────────────────────────────────────────────────── */
.wpaap-cfg-feat-row {
    display: flex; align-items: center; gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}
.wpaap-cfg-feat-row:last-child { border-bottom: none; }
.wpaap-cfg-feat-icon {
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wpaap-cfg-feat-info { flex: 1; min-width: 0; }
.wpaap-cfg-feat-name-row { display: flex; align-items: center; gap: 8px; margin-bottom: 3px; }
.wpaap-cfg-feat-name { font-size: 14px; font-weight: 700; color: #0f172a; }
.wpaap-cfg-feat-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; margin: 0; }
.wpaap-cfg-plan-badge {
    display: inline-flex; align-items: center;
    padding: 2px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 700; white-space: nowrap; flex-shrink: 0;
}
.wpaap-cfg-plan-badge.premium { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }

/* ── New red toggle ─────────────────────────────────────────────────── */
.wpaap-cfg-toggle-ctrl { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.wpaap-cfg-toggle-state { font-size: 12px; font-weight: 700; color: #94a3b8; min-width: 22px; text-align: right; }
.wpaap-cfg-toggle-state.wpaap-ts-active { color: #16a34a; }
.wpaap-ai-toggle { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
.wpaap-ai-toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
.wpaap-ai-slider {
    position: absolute; cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background: #cbd5e1; border-radius: 26px; transition: .25s;
}
.wpaap-ai-slider:before {
    position: absolute; content: "";
    height: 20px; width: 20px; left: 3px; bottom: 3px;
    background: #fff; border-radius: 50%; transition: .25s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.wpaap-ai-toggle input:checked + .wpaap-ai-slider { background: #22c55e; }
.wpaap-ai-toggle input:checked + .wpaap-ai-slider:before { transform: translateX(22px); }

/* ── Provider grid ──────────────────────────────────────────────────── */
.wpaap-cfg-provider-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 12px; padding: 16px 20px;
}
.wpaap-cfg-provider-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 12px; padding: 14px 16px;
    display: flex; flex-direction: column; gap: 8px;
}
.wpaap-cfg-provider-top {
    display: flex; align-items: center; justify-content: space-between;
}
.wpaap-cfg-provider-logo {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    background: #fff; border: 1px solid #e2e8f0; flex-shrink: 0;
}
.wpaap-cfg-provider-name { font-size: 14px; font-weight: 700; color: #0f172a; }
.wpaap-cfg-provider-model { font-size: 12px; color: #64748b; }
.wpaap-cfg-conn-badge {
    font-size: 11px; font-weight: 600;
    padding: 2px 9px; border-radius: 20px; white-space: nowrap;
}
.wpaap-cfg-conn-badge.connected    { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.wpaap-cfg-conn-badge.disconnected { background: #f1f5f9; color: #64748b; }
.wpaap-cfg-conn-link {
    display: block; padding: 10px 20px 16px;
    font-size: 13px; color: #64748b; text-decoration: none;
}
.wpaap-cfg-conn-link:hover { color: #e11d48; }

/* ── Protective banner ──────────────────────────────────────────────── */
.wpaap-cfg-protect-banner {
    background: linear-gradient(110deg, #fff1f2, #ffe4e6);
    border: 1px solid #fecdd3; border-radius: 14px;
    padding: 18px 22px; margin-bottom: 20px;
    display: flex; justify-content: space-between; align-items: center; gap: 16px;
}
.wpaap-cfg-protect-left { display: flex; align-items: center; gap: 14px; flex: 1; min-width: 0; }
.wpaap-cfg-protect-title { font-size: 14px; font-weight: 700; color: #881337; margin: 0 0 3px; }
.wpaap-cfg-protect-desc  { font-size: 12px; color: #9f1239; margin: 0; line-height: 1.5; }
.wpaap-cfg-protect-btn {
    display: inline-flex; align-items: center;
    background: #e11d48; color: #fff; text-decoration: none;
    padding: 10px 18px; border-radius: 8px;
    font-size: 13px; font-weight: 700; white-space: nowrap; flex-shrink: 0;
    transition: background .15s;
}
.wpaap-cfg-protect-btn:hover { background: #be123c; color: #fff; }

/* ── Sidebar boxes ──────────────────────────────────────────────────── */
.wpaap-cfg-sidebar { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 40px; }
.wpaap-cfg-sidebar-box {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 14px; padding: 16px 18px;
}
.wpaap-cfg-sidebar-box-head {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;
}
.wpaap-cfg-sidebar-box-head strong { font-size: 14px; font-weight: 700; color: #0f172a; }
.wpaap-cfg-sidebar-box-head a { font-size: 12px; color: #94a3b8; text-decoration: none; }
.wpaap-cfg-sidebar-box-head a:hover { color: #e11d48; }

/* Status bar */
.wpaap-cfg-status-bar {
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 8px; padding: 10px 14px; margin-bottom: 10px;
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 600; color: #15803d;
}

/* Feature list */
.wpaap-cfg-feat-list-item {
    display: flex; justify-content: space-between; align-items: center;
    padding: 8px 0; border-bottom: 1px solid #f1f5f9;
}
.wpaap-cfg-feat-list-item:last-child { border-bottom: none; }
.wpaap-cfg-feat-list-left { display: flex; align-items: center; gap: 8px; }
.wpaap-cfg-feat-dot { width: 6px; height: 6px; border-radius: 50%; background: #22c55e; flex-shrink: 0; }
.wpaap-cfg-feat-list-name { font-size: 13px; color: #1e293b; }
.wpaap-cfg-feat-on  { font-size: 12px; font-weight: 600; color: #16a34a; }
.wpaap-cfg-feat-off { font-size: 12px; color: #94a3b8; }
.wpaap-cfg-sidebar-foot { margin-top: 10px; }
.wpaap-cfg-sidebar-foot a { font-size: 12.5px; color: #94a3b8; text-decoration: none; }
.wpaap-cfg-sidebar-foot a:hover { color: #e11d48; }

/* Stat rows */
.wpaap-cfg-stat-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 9px 0; border-bottom: 1px solid #f8fafc;
}
.wpaap-cfg-stat-row:last-child { border-bottom: none; }
.wpaap-cfg-stat-label { font-size: 13px; color: #64748b; }
.wpaap-cfg-stat-right { display: flex; align-items: center; gap: 6px; }
.wpaap-cfg-stat-num { font-size: 14px; font-weight: 700; color: #0f172a; }
.wpaap-cfg-trend {
    font-size: 11px; font-weight: 600;
    padding: 2px 7px; border-radius: 20px; white-space: nowrap;
}
.wpaap-cfg-trend.up   { background: #f0fdf4; color: #16a34a; }
.wpaap-cfg-trend.down { background: #fff1f2; color: #e11d48; }

@media (max-width: 900px) {
    .wpaap-cfg-grid { grid-template-columns: 1fr; }
    .wpaap-cfg-provider-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 560px) {
    .wpaap-cfg-provider-grid { grid-template-columns: 1fr; }
    .wpaap-cfg-protect-banner { flex-direction: column; align-items: flex-start; }
}
</style>

<form method="post" class="wpaap-cfg-wrap" id="wpaap-config-form">
    <?php wp_nonce_field('_token', '_token'); ?>
    <input type="hidden" id="wpaap-enable-sync" name="whp_aipay_enable" value="<?php echo esc_attr($whp_aipay_enable === '1' ? '1' : '0'); ?>">

    <?php if ($isSubmit): ?>
    <div class="wpaap-cfg-notify">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <?php esc_html_e('Đã lưu cài đặt thành công.', 'whp'); ?>
    </div>
    <?php endif; ?>

    <div class="wpaap-cfg-grid">

        <!-- ════ LEFT COLUMN ════ -->
        <div class="wpaap-cfg-main">

            <!-- Section 1: Tính năng AI -->
            <div class="wpaap-cfg-section">
                <div class="wpaap-cfg-section-head">
                    <div class="wpaap-cfg-section-head-icon" style="background:#fff7ed;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#f59e0b" stroke="none">
                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Tính năng AI', 'whp'); ?></h3>
                        <p><?php esc_html_e('Bật/tắt các tính năng AI để hệ thống hoạt động tối ưu nhất.', 'whp'); ?></p>
                    </div>
                </div>
                <div class="wpaap-cfg-section-body">

                    <!-- OCR -->
                    <div class="wpaap-cfg-feat-row">
                        <div class="wpaap-cfg-feat-icon" style="background:#fff1f2;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="4" width="16" height="16" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/>
                            </svg>
                        </div>
                        <div class="wpaap-cfg-feat-info">
                            <div class="wpaap-cfg-feat-name-row">
                                <span class="wpaap-cfg-feat-name"><?php esc_html_e('AI OCR Biên Lai', 'whp'); ?></span>
                            </div>
                            <p class="wpaap-cfg-feat-desc"><?php esc_html_e('Tự động đọc và trích xuất thông tin từ ảnh biên lai chuyển khoản số tiền, STK, thời gian giao dịch, nội dung chuyển khoản.', 'whp'); ?></p>
                        </div>
                        <div class="wpaap-cfg-toggle-ctrl">
                            <span class="wpaap-cfg-toggle-state<?php echo $whp_aipay_ocr_enable === '1' ? ' wpaap-ts-active' : ''; ?>">
                                <?php echo $whp_aipay_ocr_enable === '1' ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                            </span>
                            <label class="wpaap-ai-toggle" title="<?php esc_attr_e('Bật AI OCR Biên Lai', 'whp'); ?>">
                                <input type="checkbox" name="whp_aipay_ocr_enable" value="1" <?php checked($whp_aipay_ocr_enable, '1'); ?>>
                                <span class="wpaap-ai-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Fraud Detection -->
                    <div class="wpaap-cfg-feat-row">
                        <div class="wpaap-cfg-feat-icon" style="background:#fff1f2;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                        </div>
                        <div class="wpaap-cfg-feat-info">
                            <div class="wpaap-cfg-feat-name-row">
                                <span class="wpaap-cfg-feat-name">AI Fraud Detection</span>
                            </div>
                            <p class="wpaap-cfg-feat-desc"><?php esc_html_e('Phân tích hình ảnh & dữ liệu để phát hiện gian lận, tiền giả, biên lai mạo, chỉnh sửa hoặc giao dịch bất thường.', 'whp'); ?></p>
                        </div>
                        <div class="wpaap-cfg-toggle-ctrl">
                            <span class="wpaap-cfg-toggle-state<?php echo $whp_aipay_fraud_enable === '1' ? ' wpaap-ts-active' : ''; ?>">
                                <?php echo $whp_aipay_fraud_enable === '1' ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                            </span>
                            <label class="wpaap-ai-toggle" title="<?php esc_attr_e('Bật AI Fraud Detection', 'whp'); ?>">
                                <input type="checkbox" name="whp_aipay_fraud_enable" value="1" <?php checked($whp_aipay_fraud_enable, '1'); ?>>
                                <span class="wpaap-ai-slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Copilot -->
                    <div class="wpaap-cfg-feat-row">
                        <div class="wpaap-cfg-feat-icon" style="background:#f0f9ff;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                            </svg>
                        </div>
                        <div class="wpaap-cfg-feat-info">
                            <div class="wpaap-cfg-feat-name-row">
                                <span class="wpaap-cfg-feat-name">AI Copilot</span>
                            </div>
                            <p class="wpaap-cfg-feat-desc"><?php esc_html_e('Hỗ trợ xử lý, đề xuất duyệt/từ chối, hoàn tiền, ghi chú và tổng hợp thông tin giao dịch.', 'whp'); ?></p>
                        </div>
                        <div class="wpaap-cfg-toggle-ctrl">
                            <span class="wpaap-cfg-toggle-state<?php echo $whp_aipay_copilot_enable === '1' ? ' wpaap-ts-active' : ''; ?>">
                                <?php echo $whp_aipay_copilot_enable === '1' ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                            </span>
                            <label class="wpaap-ai-toggle" title="<?php esc_attr_e('Bật AI Copilot', 'whp'); ?>">
                                <input type="checkbox" name="whp_aipay_copilot_enable" value="1" <?php checked($whp_aipay_copilot_enable, '1'); ?>>
                                <span class="wpaap-ai-slider"></span>
                            </label>
                        </div>
                    </div>

                </div><!-- /.wpaap-cfg-section-body -->
            </div><!-- /.wpaap-cfg-section (features) -->

            <!-- Section 2: Nhà cung cấp AI -->
            <div class="wpaap-cfg-section">
                <div class="wpaap-cfg-section-head">
                    <div class="wpaap-cfg-section-head-icon" style="background:#f8fafc;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                        </svg>
                    </div>
                    <div>
                        <h3><?php esc_html_e('Nhà cung cấp AI', 'whp'); ?></h3>
                        <p><?php esc_html_e('Kết nối và quản lý API Keys của các nhà cung cấp AI.', 'whp'); ?></p>
                    </div>
                </div>

                <div class="wpaap-cfg-provider-grid">
                    <?php foreach ($providers as $k => $prov):
                        $connected = $prov['connected'];
                    ?>
                    <div class="wpaap-cfg-provider-card">
                        <div class="wpaap-cfg-provider-top">
                            <div class="wpaap-cfg-provider-logo">
                                <?php echo $prov['logo']; ?>
                            </div>
                            <span class="wpaap-cfg-conn-badge <?php echo $connected ? 'connected' : 'disconnected'; ?>">
                                <?php echo $connected ? esc_html__('✓ Đã kết nối', 'whp') : esc_html__('Chưa kết nối', 'whp'); ?>
                            </span>
                        </div>
                        <div class="wpaap-cfg-provider-name"><?php echo esc_html($prov['label']); ?></div>
                        <div class="wpaap-cfg-provider-model">Model: <?php echo esc_html($prov['model']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <a href="<?php echo esc_url($conn_url); ?>" class="wpaap-cfg-conn-link">
                    🔑 <?php esc_html_e('Quản lý API Keys → AI Kết nối', 'whp'); ?>
                </a>
            </div><!-- /.wpaap-cfg-section (providers) -->

            <!-- Section 3: Protective banner -->
            <div class="wpaap-cfg-protect-banner">
                <div class="wpaap-cfg-protect-left">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <div>
                        <p class="wpaap-cfg-protect-title"><?php esc_html_e('AI Thanh Toán đang bảo vệ cửa hàng của bạn', 'whp'); ?></p>
                        <p class="wpaap-cfg-protect-desc"><?php esc_html_e('Tất cả tính năng AI OCR, Fraud Detection và Copilot đang hoạt động ổn định.', 'whp'); ?></p>
                    </div>
                </div>
                <a href="<?php echo esc_url($logs_url); ?>" class="wpaap-cfg-protect-btn"><?php esc_html_e('Xem nhật ký →', 'whp'); ?></a>
            </div>

        </div><!-- /.wpaap-cfg-main -->

        <!-- ════ RIGHT SIDEBAR ════ -->
        <div class="wpaap-cfg-sidebar">

            <!-- Box 1: Trạng thái AI Payment -->
            <div class="wpaap-cfg-sidebar-box">
                <div class="wpaap-cfg-sidebar-box-head">
                    <strong><?php esc_html_e('Trạng thái AI Payment', 'whp'); ?></strong>
                    <a href="<?php echo esc_url($cfg_url); ?>"><?php esc_html_e('Cài đặt tính năng', 'whp'); ?></a>
                </div>

                <div class="wpaap-cfg-status-bar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php esc_html_e('Tất cả hệ thống hoạt động tốt', 'whp'); ?>
                </div>

                <?php
                $feat_list = [
                    __('AI OCR Biên Lai', 'whp')    => $whp_aipay_ocr_enable     === '1',
                    'AI Fraud Detection'             => $whp_aipay_fraud_enable    === '1',
                    'AI Copilot'                     => $whp_aipay_copilot_enable  === '1',
                ];
                foreach ($feat_list as $fname => $enabled): ?>
                <div class="wpaap-cfg-feat-list-item">
                    <div class="wpaap-cfg-feat-list-left">
                        <span class="wpaap-cfg-feat-dot"></span>
                        <span class="wpaap-cfg-feat-list-name"><?php echo esc_html($fname); ?></span>
                    </div>
                    <?php if ($enabled): ?>
                        <span class="wpaap-cfg-feat-on"><?php esc_html_e('Hoạt động', 'whp'); ?></span>
                    <?php else: ?>
                        <span class="wpaap-cfg-feat-off"><?php esc_html_e('Tắt', 'whp'); ?></span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="wpaap-cfg-sidebar-foot">
                    <a href="<?php echo esc_url($cfg_url); ?>">⚙ <?php esc_html_e('Cài đặt tính năng', 'whp'); ?></a>
                </div>
            </div><!-- /.wpaap-cfg-sidebar-box (status) -->

            <!-- Box 2: Thống kê nhanh -->
            <div class="wpaap-cfg-sidebar-box">
                <div class="wpaap-cfg-sidebar-box-head">
                    <strong><?php esc_html_e('Thống kê nhanh', 'whp'); ?></strong>
                    <span style="font-size:12px;color:#94a3b8;"><?php esc_html_e('Hôm nay ▾', 'whp'); ?></span>
                </div>

                <?php
                $stats = [
                    __('Tổng đơn hàng', 'whp')    => [$s_total_count, $stat_trend($s_total_count, $y_total_count)],
                    __('Đã xác minh', 'whp')       => [$s_verified,    $stat_trend($s_verified,    $y_verified)],
                    __('Đang chờ', 'whp')          => [$s_pending,     null],
                    __('Rủi ro / Gian lận', 'whp') => [$s_risk,        $stat_trend($s_risk,        $y_risk)],
                ];
                foreach ($stats as $label => [$num, $trend]): ?>
                <div class="wpaap-cfg-stat-row">
                    <span class="wpaap-cfg-stat-label"><?php echo esc_html($label); ?></span>
                    <div class="wpaap-cfg-stat-right">
                        <span class="wpaap-cfg-stat-num"><?php echo intval($num); ?></span>
                        <?php if ($trend): ?>
                        <span class="wpaap-cfg-trend <?php echo esc_attr($trend['dir']); ?>">
                            <?php echo $trend['dir'] === 'up' ? '↑' : '↓'; ?><?php echo intval($trend['pct']); ?>%
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Mini sparkline -->
                <div style="margin-top:12px;">
                    <svg viewBox="0 0 200 45" width="100%" height="45" xmlns="http://www.w3.org/2000/svg" style="display:block;">
                        <polyline
                            points="0,38 25,28 50,32 75,18 100,24 125,12 150,20 175,10 200,16"
                            fill="none" stroke="#e11d48" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" opacity=".55"/>
                        <polyline
                            points="0,38 25,28 50,32 75,18 100,24 125,12 150,20 175,10 200,16 200,45 0,45"
                            fill="url(#wpaap-spark-grad)" stroke="none" opacity=".12"/>
                        <defs>
                            <linearGradient id="wpaap-spark-grad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#e11d48"/>
                                <stop offset="100%" stop-color="#e11d48" stop-opacity="0"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div><!-- /.wpaap-cfg-sidebar-box (stats) -->

        </div><!-- /.wpaap-cfg-sidebar -->

    </div><!-- /.wpaap-cfg-grid -->

<script>
var whpI18n = <?php echo wp_json_encode([
    'on'  => __( 'Bật', 'whp' ),
    'off' => __( 'Tắt', 'whp' ),
]); ?>;
(function(){
    document.querySelectorAll('.wpaap-ai-toggle input[type="checkbox"]').forEach(function(cb){
        cb.addEventListener('change', function(){
            var state = this.closest('.wpaap-cfg-feat-row')?.querySelector('.wpaap-cfg-toggle-state');
            if(state){ state.textContent = this.checked ? whpI18n.on : whpI18n.off; state.classList.toggle('wpaap-ts-active',this.checked); }
        });
    });
})();
</script>

</form>
    <?php
}
