<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── UI HELPERS ──────────────────────────────────────────────────────────────

function wph_cap_eye_icon( $slashed = false ) {
    return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}

// ─── SETTINGS HELPER ─────────────────────────────────────────────────────────

function wph_cap_settings() {
    return get_option( 'wph_captcha_settings', array() );
}

function wph_cap_active_provider() {
    $s = wph_cap_settings();
    // Master toggle must be ON
    if ( empty( $s['active'] ) || $s['active'] !== '1' ) return null;
    // New structure: master active flag + active_provider key
    if ( ! empty( $s['active_provider'] ) ) {
        $p = $s['active_provider'];
        // Provider must have enabled='1' AND both keys filled
        $is_enabled = ! isset( $s[ $p ]['enabled'] ) || $s[ $p ]['enabled'] === '1'; // backward compat: no enabled key → assume on
        if ( $is_enabled && ! empty( $s[ $p ]['site_key'] ) && ! empty( $s[ $p ]['secret_key'] ) ) {
            return $p;
        }
        // active_provider set but enabled=0 or keys missing → CAPTCHA is OFF
        return null;
    }
    // Legacy fallback: per-provider active flag (old data format before active_provider key)
    foreach ( array( 'recaptcha_v3', 'recaptcha_v2', 'turnstile', 'hcaptcha' ) as $p ) {
        if ( ! empty( $s[ $p ]['active'] ) && $s[ $p ]['active'] === '1'
             && ! empty( $s[ $p ]['site_key'] ) && ! empty( $s[ $p ]['secret_key'] ) ) {
            return $p;
        }
    }
    return null;
}

// ─── ENQUEUE SCRIPTS ─────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts',    'wph_cap_enqueue' );
add_action( 'login_enqueue_scripts', 'wph_cap_enqueue' ); // login page uses a separate hook
function wph_cap_enqueue() {
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    $s = wph_cap_settings();

    switch ( $p ) {
        case 'recaptcha_v2':
            wp_enqueue_script( 'wph-recaptcha-v2', 'https://www.google.com/recaptcha/api.js', array(), null, true );
            break;
        case 'recaptcha_v3':
            $key = $s['recaptcha_v3']['site_key'] ?? '';
            wp_enqueue_script( 'wph-recaptcha-v3', "https://www.google.com/recaptcha/api.js?render={$key}", array(), null, true );
            // Refresh token every 90 s (v3 TTL = 2 min) and after each form submission
            // so CF7/WPForms/GF/Fluent always get a fresh token even on multi-submit pages.
            wp_add_inline_script( 'wph-recaptcha-v3',
                "grecaptcha.ready(function(){" .
                    "var _wk=" . json_encode( $key ) . ";" .
                    "function _wphV3R(){" .
                        "grecaptcha.execute(_wk,{action:'submit'}).then(function(t){" .
                            "document.querySelectorAll('.wph-recaptcha-v3-token').forEach(function(el){el.value=t;});" .
                        "});" .
                    "}" .
                    "_wphV3R();" .
                    "setInterval(_wphV3R,90000);" .
                    // CF7: fires wpcf7submit after every submission (success or error)
                    "document.addEventListener('wpcf7submit',function(){setTimeout(_wphV3R,100);});" .
                    // Fluent Forms: CustomEvent fired after submission
                    "document.addEventListener('fluentform_submission_success',function(){setTimeout(_wphV3R,100);});" .
                    // GF: re-renders form HTML via AJAX on pagination/conditional logic
                    "(function gf(){if(window.jQuery){jQuery(document).on('gform_post_render',function(){setTimeout(_wphV3R,100);});}else{setTimeout(gf,600);}})();" .
                    // WPForms: jQuery custom event after AJAX submit
                    "(function wf(){if(window.jQuery){jQuery(document).on('wpformsAjaxSubmitCompleted',function(){setTimeout(_wphV3R,100);});}else{setTimeout(wf,600);}})();" .
                "});"
            );
            break;
        case 'turnstile':
            wp_enqueue_script( 'wph-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );
            break;
        case 'hcaptcha':
            wp_enqueue_script( 'wph-hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), null, true );
            break;
    }
}

// ─── CF7 INTEGRATION ─────────────────────────────────────────────────────────

add_filter( 'wpcf7_form_elements', 'wph_cap_inject_cf7_widget' );
function wph_cap_inject_cf7_widget( $html ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['cf7'] ) || $s['apply']['cf7'] !== '1' ) return $html;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $html;

    $widget = wph_cap_render_widget( $p, $s );
    // Inject before submit button
    $html = preg_replace( '/(<input[^>]+type=["\']submit["\'][^>]*>)/i', $widget . '$1', $html, 1 );
    return $html;
}

add_filter( 'wpcf7_spam', 'wph_cap_verify_cf7', 9 );
function wph_cap_verify_cf7( $is_spam ) {
    if ( $is_spam ) return $is_spam;
    $s = wph_cap_settings();
    if ( empty( $s['apply']['cf7'] ) || $s['apply']['cf7'] !== '1' ) return $is_spam;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $is_spam;

    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail CF7: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        return true; // mark as spam
    }
    wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    return $is_spam;
}

// ─── WP LOGIN INTEGRATION ────────────────────────────────────────────────────

add_action( 'login_form', 'wph_cap_inject_login' );
function wph_cap_inject_login() {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['login'] ) || $s['apply']['login'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    echo wph_cap_render_widget( $p, $s );
}

add_filter( 'authenticate', 'wph_cap_verify_login', 21, 3 );
function wph_cap_verify_login( $user, $username, $password ) {
    if ( ! $username ) return $user;
    $s = wph_cap_settings();
    if ( empty( $s['apply']['login'] ) || $s['apply']['login'] !== '1' ) return $user;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $user;

    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail login: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        return new WP_Error( 'captcha_failed', 'Xác minh CAPTCHA thất bại. Vui lòng thử lại.' );
    }
    wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    return $user;
}

// ─── WP REGISTER INTEGRATION ─────────────────────────────────────────────────

add_action( 'register_form', 'wph_cap_inject_register' );
function wph_cap_inject_register() {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['register'] ) || $s['apply']['register'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    echo wph_cap_render_widget( $p, $s );
}

add_filter( 'registration_errors', 'wph_cap_verify_register', 10, 3 );
function wph_cap_verify_register( $errors, $sanitized_user_login, $user_email ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['register'] ) || $s['apply']['register'] !== '1' ) return $errors;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $errors;

    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail register: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        $errors->add( 'captcha_error', 'Xác minh CAPTCHA thất bại.' );
    } else {
        wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    }
    return $errors;
}

// ─── WOOCOMMERCE MY ACCOUNT LOGIN / REGISTER ─────────────────────────────────
// WooCommerce My Account page (/?page_id=xx) render form riêng, không fire
// login_form / register_form của wp-login.php nên cần hook WC riêng.
// Verify login: WC dùng wp_signon() nên authenticate filter ở trên vẫn catch.
// Verify register: WC KHÔNG gọi registration_errors filter → dùng woocommerce_register_post.

add_action( 'woocommerce_login_form', 'wph_cap_inject_wc_login' );
function wph_cap_inject_wc_login() {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['login'] ) || $s['apply']['login'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    echo wph_cap_render_widget( $p, $s );
}

add_action( 'woocommerce_register_form', 'wph_cap_inject_wc_register' );
function wph_cap_inject_wc_register() {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['register'] ) || $s['apply']['register'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    echo wph_cap_render_widget( $p, $s );
}

add_action( 'woocommerce_register_post', 'wph_cap_verify_wc_register', 10, 3 );
function wph_cap_verify_wc_register( $username, $email, $errors ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['register'] ) || $s['apply']['register'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail wc-register: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        $errors->add( 'captcha_error', 'Xác minh CAPTCHA thất bại.' );
    } else {
        wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    }
}

// ─── COMMENT FORM INTEGRATION ────────────────────────────────────────────────
// Dùng filter comment_form_submit_button để inject ngay trước nút Gửi,
// works cho cả non-logged-in users (logged-in users không cần CAPTCHA).

add_filter( 'comment_form_submit_button', 'wph_cap_inject_comment', 10, 2 );
function wph_cap_inject_comment( $button, $args = null ) {
    if ( is_user_logged_in() ) return $button;
    $s = wph_cap_settings();
    if ( empty( $s['apply']['comment'] ) || $s['apply']['comment'] !== '1' ) return $button;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $button;
    return wph_cap_render_widget( $p, $s ) . $button;
}

add_filter( 'preprocess_comment', 'wph_cap_verify_comment' );
function wph_cap_verify_comment( $comment_data ) {
    if ( is_user_logged_in() ) return $comment_data;
    $s = wph_cap_settings();
    if ( empty( $s['apply']['comment'] ) || $s['apply']['comment'] !== '1' ) return $comment_data;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $comment_data;

    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail comment: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        wp_die( 'Xác minh CAPTCHA thất bại. Quay lại và thử lại.', 'CAPTCHA Error', array( 'back_link' => true ) );
    }
    wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    return $comment_data;
}

// ─── WPFORMS INTEGRATION ─────────────────────────────────────────────────────

add_action( 'wpforms_display_submit_before', 'wph_cap_inject_wpforms', 10, 1 );
function wph_cap_inject_wpforms( $form_data ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['wpforms'] ) || $s['apply']['wpforms'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    echo wph_cap_render_widget( $p, $s );
}

add_action( 'wpforms_process_before', 'wph_cap_verify_wpforms', 5, 2 );
function wph_cap_verify_wpforms( $entry_data, $form_data ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['wpforms'] ) || $s['apply']['wpforms'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail WPForms: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        wpforms()->get( 'process' )->errors[ $form_data['id'] ]['footer'] = 'Xác minh CAPTCHA thất bại. Vui lòng thử lại.';
    } else {
        wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    }
}

// ─── GRAVITY FORMS INTEGRATION ────────────────────────────────────────────────

add_filter( 'gform_get_form_filter', 'wph_cap_inject_gravityforms', 10, 2 );
function wph_cap_inject_gravityforms( $form_html, $form ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['gf'] ) || $s['apply']['gf'] !== '1' ) return $form_html;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $form_html;
    $widget = wph_cap_render_widget( $p, $s );
    // Inject before GF submit button
    $form_html = preg_replace( '/(<input[^>]+id=["\']gform_submit[^>]*>)/i', $widget . '$1', $form_html, 1 );
    return $form_html;
}

add_filter( 'gform_validation', 'wph_cap_verify_gravityforms', 10, 1 );
function wph_cap_verify_gravityforms( $validation_result ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['gf'] ) || $s['apply']['gf'] !== '1' ) return $validation_result;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $validation_result;
    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail GF: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        $validation_result['is_valid'] = false;
        // Mark first field as failed so GF shows the error
        $form   = $validation_result['form'];
        $fields = &$validation_result['form']['fields'];
        if ( ! empty( $fields ) ) {
            $fields[0]->failed_validation  = true;
            $fields[0]->validation_message = 'Xác minh CAPTCHA thất bại. Vui lòng thử lại.';
        }
    } else {
        wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    }
    return $validation_result;
}

// ─── NINJA FORMS INTEGRATION ──────────────────────────────────────────────────

add_action( 'ninja_forms_display_before_fields', 'wph_cap_inject_ninjaforms', 10, 1 );
function wph_cap_inject_ninjaforms( $form_id ) {
    // NF renders via JS/AJAX — PHP hook fires during initial SSR but NF re-renders
    // the form via React after page load, replacing this output. The wp_footer JS
    // fallback (wph_cap_universal_js_inject) handles the actual widget injection.
    // This hook is intentionally left as a no-op; do not add echo/print here.
}

add_filter( 'ninja_forms_submit_data', 'wph_cap_verify_ninjaforms', 10, 1 );
function wph_cap_verify_ninjaforms( $form_data ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['ninja'] ) || $s['apply']['ninja'] !== '1' ) return $form_data;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $form_data;
    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail NinjaForms: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        $form_data['errors']['fields']['wph_captcha'] = 'Xác minh CAPTCHA thất bại. Vui lòng thử lại.';
    } else {
        wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    }
    return $form_data;
}

// ─── FLUENT FORMS INTEGRATION ─────────────────────────────────────────────────

add_action( 'fluentform/render_item_submit_button', 'wph_cap_inject_fluentforms', 9, 2 );
function wph_cap_inject_fluentforms( $item, $form ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['fluent'] ) || $s['apply']['fluent'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    echo wph_cap_render_widget( $p, $s );
}

add_filter( 'fluentform/before_insert_submission', 'wph_cap_verify_fluentforms', 5, 3 );
function wph_cap_verify_fluentforms( $insertData, $data, $form ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['fluent'] ) || $s['apply']['fluent'] !== '1' ) return $insertData;
    $p = wph_cap_active_provider();
    if ( ! $p ) return $insertData;
    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail FluentForms: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        wp_send_json( array(
            'errors' => array( 'captcha' => array( 'Xác minh CAPTCHA thất bại. Vui lòng thử lại.' ) ),
        ), 422 );
        exit;
    }
    wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    return $insertData;
}

// ─── ELEMENTOR FORMS INTEGRATION ─────────────────────────────────────────────

add_action( 'elementor_pro/forms/validation', 'wph_cap_verify_elementor', 10, 2 );
function wph_cap_verify_elementor( $record, $ajax_handler ) {
    $s = wph_cap_settings();
    if ( empty( $s['apply']['elementor'] ) || $s['apply']['elementor'] !== '1' ) return;
    $p = wph_cap_active_provider();
    if ( ! $p ) return;
    $result = wph_cap_verify( $p, $s );
    if ( ! $result['success'] ) {
        wph_cap_log( wph_fm_get_ip(), 'CAPTCHA fail Elementor: ' . $result['reason'], 'failed', $p, $result['score'] ?? null );
        $ajax_handler->add_error_message( 'Xác minh CAPTCHA thất bại. Vui lòng thử lại.' );
    } else {
        wph_cap_log( wph_fm_get_ip(), '', 'passed', $p, $result['score'] ?? null );
    }
}

// ─── UNIVERSAL JS WIDGET INJECTION ───────────────────────────────────────────
// Covers Ninja Forms + Elementor (both render via JS/AJAX, not PHP hooks).
// Fixes:
//   • reCAPTCHA v2: call grecaptcha.render() after dynamic DOM injection (widget
//     does NOT auto-render when div is added after page load)
//   • Ninja Forms: append captcha token to jQuery AJAX payload for nf_ajax_submit
//     (NF collects its own fields via JS, not raw DOM inputs)
//   • Elementor + reCAPTCHA v3: intercept form submit to refresh token (v3 token
//     has 2-min TTL; user may fill form longer than that)

add_action( 'wp_footer', 'wph_cap_universal_js_inject', 20 );
function wph_cap_universal_js_inject() {
    $s = wph_cap_settings();
    $p = wph_cap_active_provider();
    if ( ! $p ) return;

    $do_ninja     = ! empty( $s['apply']['ninja'] )     && $s['apply']['ninja']     === '1';
    $do_elementor = ! empty( $s['apply']['elementor'] ) && $s['apply']['elementor'] === '1';
    if ( ! $do_ninja && ! $do_elementor ) return;

    $widget   = wph_cap_render_widget( $p, $s );
    $site_key = '';
    if ( $p === 'recaptcha_v2' ) $site_key = $s['recaptcha_v2']['site_key'] ?? '';
    if ( $p === 'recaptcha_v3' ) $site_key = $s['recaptcha_v3']['site_key'] ?? '';
    ?>
    <script>
    (function(){
    var _P  = <?php echo json_encode( $p ); ?>;
    var _SK = <?php echo json_encode( $site_key ); ?>;
    var _W  = <?php echo json_encode( $widget ); ?>;
    var _NF = <?php echo $do_ninja     ? 'true' : 'false'; ?>;
    var _EL = <?php echo $do_elementor ? 'true' : 'false'; ?>;

    /* ── render widget after dynamic injection ───────────────────────────── */
    function wphCapRender(div) {
        if (_P === 'recaptcha_v2') {
            var el = div.querySelector('.g-recaptcha');
            if (!el) return;
            (function t(n) {
                if (window.grecaptcha && typeof grecaptcha.render === 'function') {
                    try { grecaptcha.render(el, {sitekey: _SK}); } catch(e) {}
                } else if (n < 20) { setTimeout(function(){ t(n+1); }, 300); }
            })(0);
        } else if (_P === 'turnstile') {
            var el = div.querySelector('.cf-turnstile');
            if (!el) return;
            (function t(n) {
                if (window.turnstile) { try { turnstile.render(el); } catch(e) {} }
                else if (n < 15) { setTimeout(function(){ t(n+1); }, 500); }
            })(0);
        } else if (_P === 'hcaptcha') {
            var el = div.querySelector('.h-captcha');
            if (!el) return;
            (function t(n) {
                if (window.hcaptcha) { try { hcaptcha.render(el); } catch(e) {} }
                else if (n < 15) { setTimeout(function(){ t(n+1); }, 500); }
            })(0);
        } else if (_P === 'recaptcha_v3') {
            wphCapRefreshV3(div);
        }
    }

    function wphCapRefreshV3(scope) {
        if (!_SK) return;
        (function t(n) {
            if (window.grecaptcha && typeof grecaptcha.ready === 'function') {
                grecaptcha.ready(function() {
                    grecaptcha.execute(_SK, {action:'submit'}).then(function(tok) {
                        (scope || document).querySelectorAll('.wph-recaptcha-v3-token').forEach(function(i){ i.value = tok; });
                    });
                });
            } else if (n < 20) { setTimeout(function(){ t(n+1); }, 500); }
        })(0);
    }

    /* ── inject widget div before submit button ──────────────────────────── */
    function wphCapInject(form) {
        if (form.querySelector('.wph-cap-injected')) return;
        var btn = form.querySelector(
            '.nf-field-submit [type="submit"], .nf-submit [type="submit"], ' +
            '.elementor-button-wrapper [type="submit"], [type="submit"], button[type="submit"]'
        );
        if (!btn) return;
        var div = document.createElement('div');
        div.className = 'wph-cap-injected';
        div.innerHTML = _W;
        btn.parentNode.insertBefore(div, btn);
        wphCapRender(div);
    }

    function wphCapRun() {
        var sels = [];
        if (_NF) sels.push('.nf-form-cont');
        if (_EL) sels.push('.elementor-form');
        document.querySelectorAll(sels.join(',')).forEach(wphCapInject);
    }

    /* ── token helpers ───────────────────────────────────────────────────── */
    function wphCapTokParam() {
        if (_P === 'recaptcha_v2') return 'g-recaptcha-response';
        if (_P === 'recaptcha_v3') return 'wph_recaptcha_v3_token';
        if (_P === 'turnstile')    return 'cf-turnstile-response';
        if (_P === 'hcaptcha')     return 'h-captcha-response';
        return 'wph_captcha_token';
    }

    function wphCapGetToken(scope) {
        var c = (scope && document.querySelector(scope)) || document;
        if (_P === 'recaptcha_v2') {
            var r = c.querySelector('textarea[name="g-recaptcha-response"]');
            if (r) return r.value;
            // fallback: grecaptcha.getResponse() for first widget on page
            return (window.grecaptcha && grecaptcha.getResponse) ? grecaptcha.getResponse() : '';
        }
        if (_P === 'recaptcha_v3') { var r = c.querySelector('.wph-recaptcha-v3-token'); return r ? r.value : ''; }
        if (_P === 'turnstile')    { var r = c.querySelector('[name="cf-turnstile-response"]'); return r ? r.value : ''; }
        if (_P === 'hcaptcha')     { var r = c.querySelector('[name="h-captcha-response"]'); return r ? r.value : ''; }
        return '';
    }

    /* ── Ninja Forms: append token to NF jQuery AJAX submission ─────────── */
    // NF collects its own registered fields via JS — raw DOM inputs are NOT
    // automatically included in the POST body, so we must append the token.
    if (_NF) {
        window.addEventListener('load', function() {
            if (!window.jQuery) return;
            jQuery(document).ajaxSend(function(evt, xhr, opts) {
                if (!opts.data || opts.data.indexOf('action=nf_ajax_submit') < 0) return;
                var tok = wphCapGetToken('.nf-form-cont');
                if (tok) opts.data += '&' + wphCapTokParam() + '=' + encodeURIComponent(tok);
            });
        });
        // NF re-renders forms on AJAX tab switch — re-inject on each nfFormReady
        window.addEventListener('load', function() {
            if (!window.jQuery) return;
            jQuery(document).on('nfFormReady', function() { setTimeout(wphCapRun, 200); });
        });
    }

    /* ── Elementor: refresh v3 token right before form submit ───────────── */
    // Elementor fires a native `submit` event; intercepting at capture phase
    // lets us refresh the v3 token (2-min TTL) before Elementor reads it.
    if (_EL && _P === 'recaptcha_v3') {
        var _wphElSkip = false;
        document.addEventListener('submit', function(e) {
            if (_wphElSkip) return;
            var form = e.target;
            if (!form || !form.classList || !form.classList.contains('elementor-form')) return;
            if (!window.grecaptcha || !grecaptcha.ready) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            grecaptcha.ready(function() {
                grecaptcha.execute(_SK, {action:'submit'}).then(function(tok) {
                    form.querySelectorAll('.wph-recaptcha-v3-token').forEach(function(i){ i.value = tok; });
                    _wphElSkip = true;
                    form.dispatchEvent(new Event('submit', {bubbles: true, cancelable: true}));
                    _wphElSkip = false;
                });
            });
        }, true);
    }

    /* ── run ──────────────────────────────────────────────────────────────── */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wphCapRun);
    } else {
        wphCapRun();
    }
    setTimeout(wphCapRun, 1000);   // catch forms rendered after DOMContentLoaded
    setTimeout(wphCapRun, 3000);   // catch late AJAX-rendered NF / Elementor forms

    })();
    </script>
    <?php
}

// ─── FORM RESET / RE-RENDER JS (all providers) ───────────────────────────────
// After CF7/WPForms/Fluent successful submit the form resets → visual widgets
// (reCAPTCHA v2, Turnstile, hCaptcha) must be reset so user can re-solve.
// After GF AJAX re-renders (pagination, conditional logic) the fresh widget div
// exists in DOM but has never had JS render called → we must call render again.

add_action( 'wp_footer', 'wph_cap_form_reset_js', 25 );
function wph_cap_form_reset_js() {
    $p = wph_cap_active_provider();
    if ( ! $p || $p === 'recaptcha_v3' ) return; // v3 is invisible — handled in enqueue inline script
    $s        = wph_cap_settings();
    $site_key = '';
    if ( $p === 'recaptcha_v2' ) $site_key = $s['recaptcha_v2']['site_key'] ?? '';
    ?>
    <script>
    (function(){
    var _P  = <?php echo json_encode( $p ); ?>;
    var _SK = <?php echo json_encode( $site_key ); ?>;

    /* ── reset widget so user must re-solve after form submit ── */
    function wphCapResetAll() {
        try { if (_P === 'recaptcha_v2' && window.grecaptcha) grecaptcha.reset(); } catch(e){}
        try { if (_P === 'turnstile'    && window.turnstile)  turnstile.reset();  } catch(e){}
        try { if (_P === 'hcaptcha'     && window.hcaptcha)   hcaptcha.reset();   } catch(e){}
    }

    /* ── re-render a specific widget element (for GF AJAX re-render) ── */
    function wphCapRenderEl(el) {
        if (!el || el.getAttribute('data-wph-rendered')) return;
        el.setAttribute('data-wph-rendered', '1');
        if (_P === 'recaptcha_v2') {
            (function t(n){
                if (window.grecaptcha && grecaptcha.render) {
                    try { grecaptcha.render(el, {sitekey: _SK || el.getAttribute('data-sitekey')}); } catch(e){}
                } else if (n < 15) { setTimeout(function(){ t(n+1); }, 400); }
            })(0);
        } else if (_P === 'turnstile') {
            (function t(n){
                if (window.turnstile) { try { turnstile.render(el); } catch(e){} }
                else if (n < 15) { setTimeout(function(){ t(n+1); }, 400); }
            })(0);
        } else if (_P === 'hcaptcha') {
            (function t(n){
                if (window.hcaptcha) { try { hcaptcha.render(el); } catch(e){} }
                else if (n < 15) { setTimeout(function(){ t(n+1); }, 400); }
            })(0);
        }
    }

    /* ── CF7: reset after every submission (success or validation error) ── */
    document.addEventListener('wpcf7submit', function() { setTimeout(wphCapResetAll, 200); });

    /* ── Fluent Forms: reset after success ── */
    document.addEventListener('fluentform_submission_success', function() { setTimeout(wphCapResetAll, 200); });

    /* ── GF: re-render widget in the refreshed form after AJAX page change ── */
    /* ── WPForms: reset after AJAX submit ── */
    (function bindJQ() {
        if (!window.jQuery) { setTimeout(bindJQ, 500); return; }
        jQuery(document).on('gform_post_render', function(e, formId) {
            setTimeout(function() {
                var wrap = document.getElementById('gform_wrapper_' + formId) || document;
                var sel = _P === 'recaptcha_v2' ? '.g-recaptcha'
                        : _P === 'turnstile'    ? '.cf-turnstile'
                        : _P === 'hcaptcha'     ? '.h-captcha' : null;
                if (sel) wrap.querySelectorAll(sel).forEach(function(el) {
                    // remove stale render flag so wphCapRenderEl fires again
                    el.removeAttribute('data-wph-rendered');
                    wphCapRenderEl(el);
                });
            }, 150);
        });
        jQuery(document).on('wpformsAjaxSubmitCompleted', function() { setTimeout(wphCapResetAll, 300); });
    })();

    })();
    </script>
    <?php
}

// ─── RENDER WIDGET ───────────────────────────────────────────────────────────

function wph_cap_render_widget( $provider, $s ) {
    switch ( $provider ) {
        case 'recaptcha_v2':
            $key = esc_attr( $s['recaptcha_v2']['site_key'] ?? '' );
            return '<div class="g-recaptcha" data-sitekey="' . $key . '" style="margin:10px 0;"></div>';

        case 'recaptcha_v3':
            return '<input type="hidden" class="wph-recaptcha-v3-token" name="wph_recaptcha_v3_token" value="">';

        case 'turnstile':
            $key = esc_attr( $s['turnstile']['site_key'] ?? '' );
            return '<div class="cf-turnstile" data-sitekey="' . $key . '" style="margin:10px 0;"></div>';

        case 'hcaptcha':
            $key = esc_attr( $s['hcaptcha']['site_key'] ?? '' );
            return '<div class="h-captcha" data-sitekey="' . $key . '" style="margin:10px 0;"></div>';
    }
    return '';
}

// ─── VERIFY ──────────────────────────────────────────────────────────────────

function wph_cap_verify( $provider, $s ) {
    switch ( $provider ) {
        case 'recaptcha_v2':
            $token      = sanitize_text_field( $_POST['g-recaptcha-response'] ?? '' );
            $secret_key = $s['recaptcha_v2']['secret_key'] ?? '';
            return wph_cap_call_google( $token, $secret_key );

        case 'recaptcha_v3':
            $token      = sanitize_text_field( $_POST['wph_recaptcha_v3_token'] ?? '' );
            $secret_key = $s['recaptcha_v3']['secret_key'] ?? '';
            $min_score  = (float) ( $s['recaptcha_v3']['score'] ?? 0.5 );
            $res        = wph_cap_call_google( $token, $secret_key );
            if ( $res['success'] && isset( $res['score'] ) && $res['score'] < $min_score ) {
                return array( 'success' => false, 'reason' => 'Score too low: ' . $res['score'], 'score' => $res['score'] );
            }
            return $res;

        case 'turnstile':
            $token      = sanitize_text_field( $_POST['cf-turnstile-response'] ?? '' );
            $secret_key = $s['turnstile']['secret_key'] ?? '';
            $response   = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
                'body'      => array( 'secret' => $secret_key, 'response' => $token, 'remoteip' => wph_fm_get_ip() ),
                'timeout'   => 10,
                'sslverify' => true,
            ) );
            if ( is_wp_error( $response ) ) return array( 'success' => false, 'reason' => $response->get_error_message() );
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            return array( 'success' => ! empty( $data['success'] ), 'reason' => implode( ',', $data['error-codes'] ?? array() ) );

        case 'hcaptcha':
            $token      = sanitize_text_field( $_POST['h-captcha-response'] ?? '' );
            $secret_key = $s['hcaptcha']['secret_key'] ?? '';
            $response   = wp_remote_post( 'https://api.hcaptcha.com/siteverify', array(
                'body'      => array( 'secret' => $secret_key, 'response' => $token, 'remoteip' => wph_fm_get_ip() ),
                'timeout'   => 10,
                'sslverify' => true,
            ) );
            if ( is_wp_error( $response ) ) return array( 'success' => false, 'reason' => $response->get_error_message() );
            $data = json_decode( wp_remote_retrieve_body( $response ), true );
            return array( 'success' => ! empty( $data['success'] ), 'reason' => implode( ',', $data['error-codes'] ?? array() ) );
    }
    return array( 'success' => false, 'reason' => 'Unknown provider' );
}

function wph_cap_call_google( $token, $secret ) {
    if ( ! $token ) return array( 'success' => false, 'reason' => 'Missing token' );
    $response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
        'body'      => array( 'secret' => $secret, 'response' => $token, 'remoteip' => wph_fm_get_ip() ),
        'timeout'   => 10,
        'sslverify' => true,
    ) );
    if ( is_wp_error( $response ) ) return array( 'success' => false, 'reason' => $response->get_error_message() );
    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    return array(
        'success' => ! empty( $data['success'] ),
        'score'   => $data['score'] ?? null,
        'reason'  => implode( ',', $data['error-codes'] ?? array() ),
    );
}

// ─── CAPTCHA LOG ─────────────────────────────────────────────────────────────

function wph_cap_log( $ip, $reason, $status, $provider = '', $score = null ) {
    global $wpdb;
    $data = array(
        'ip_address' => $ip,
        'reason'     => substr( $reason, 0, 255 ),
        'status'     => $status,
        'created_at' => current_time( 'mysql' ),
    );
    if ( $provider !== '' ) {
        $data['provider'] = substr( $provider, 0, 50 );
    }
    if ( $score !== null ) {
        $data['score'] = (float) $score;
    }
    $wpdb->insert( $wpdb->prefix . 'wph_captcha_logs', $data );
}

function wph_cap_create_log_table() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$wpdb->prefix}wph_captcha_logs (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        ip_address varchar(100) NOT NULL DEFAULT '',
        reason varchar(255) NOT NULL DEFAULT '',
        status varchar(20) NOT NULL DEFAULT 'failed',
        provider varchar(50) NOT NULL DEFAULT '',
        score float DEFAULT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY created_at (created_at)
    ) {$charset};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'init', 'wph_cap_create_log_table' );

function wph_cap_get_logs( $args = array() ) {
    global $wpdb;
    $per_page = absint( $args['per_page'] ?? 20 );
    $page     = max( 1, absint( $args['page'] ?? 1 ) );
    $offset   = ( $page - 1 ) * $per_page;
    $total    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_captcha_logs" );
    $rows     = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wph_captcha_logs ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ) );
    return array( 'total' => $total, 'rows' => $rows ?: array() );
}

// ─── AJAX: test connection ────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_cap_test', 'wph_cap_ajax_test' );
function wph_cap_ajax_test() {
    check_ajax_referer( 'wph_cap_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $provider = sanitize_key( $_POST['provider'] ?? '' );
    $s        = wph_cap_settings();
    $key      = $s[ $provider ]['secret_key'] ?? '';
    if ( ! $key ) { wp_send_json_error( array( 'message' => 'Chưa nhập Secret Key' ) ); return; }
    wp_send_json_success( array( 'message' => 'Cấu hình hợp lệ. Thử submit form thực để xác minh.' ) );
}

// ─── AJAX: save settings ─────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_cap_save', 'wph_cap_ajax_save' );
function wph_cap_ajax_save() {
    check_ajax_referer( 'wph_cap_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    // Read from JSON string in $_POST['settings'] (new UI pattern)
    $raw = array();
    if ( ! empty( $_POST['settings'] ) ) {
        $decoded = json_decode( wp_unslash( $_POST['settings'] ), true );
        if ( is_array( $decoded ) ) {
            $raw = $decoded;
        }
    }

    $settings = array();

    // Master toggle + active provider
    $settings['active']          = ( ! empty( $raw['active'] ) && $raw['active'] === '1' ) ? '1' : '0';
    $settings['active_provider'] = sanitize_key( $raw['active_provider'] ?? 'recaptcha_v2' );

    // Per-provider keys — only the active_provider can have enabled='1'
    $providers = array( 'recaptcha_v2', 'recaptcha_v3', 'turnstile', 'hcaptcha' );
    foreach ( $providers as $p ) {
        $pd = is_array( $raw[ $p ] ?? null ) ? $raw[ $p ] : array();
        // Enforce radio behavior: enabled only if this is the active_provider
        $enabled = ( $settings['active_provider'] === $p && ! empty( $pd['enabled'] ) && $pd['enabled'] === '1' ) ? '1' : '0';
        $settings[ $p ] = array(
            'enabled'    => $enabled,
            'site_key'   => sanitize_text_field( $pd['site_key'] ?? '' ),
            'secret_key' => sanitize_text_field( $pd['secret_key'] ?? '' ),
        );
        if ( $p === 'recaptcha_v3' ) {
            $settings[ $p ]['score'] = sanitize_text_field( $pd['score'] ?? '0.5' );
        }
        if ( $p === 'recaptcha_v2' ) {
            $settings[ $p ]['theme'] = in_array( $pd['theme'] ?? '', array( 'light', 'dark' ), true ) ? $pd['theme'] : 'light';
            $settings[ $p ]['size']  = in_array( $pd['size'] ?? '', array( 'normal', 'compact' ), true ) ? $pd['size'] : 'normal';
            $settings[ $p ]['lang']  = sanitize_text_field( $pd['lang'] ?? '' );
        }
    }

    // Apply-to (shared across providers)
    $apply_raw = is_array( $raw['apply'] ?? null ) ? $raw['apply'] : array();
    $settings['apply'] = array();
    foreach ( array( 'cf7', 'login', 'register', 'comment', 'wpforms', 'gf', 'ninja', 'fluent', 'elementor' ) as $a ) {
        $settings['apply'][ $a ] = ( ! empty( $apply_raw[ $a ] ) && $apply_raw[ $a ] === '1' ) ? '1' : '0';
    }

    update_option( 'wph_captcha_settings', $settings );
    wp_send_json_success( array( 'message' => 'Đã lưu cài đặt CAPTCHA thành công' ) );
}

// ─── AJAX: get logs (paginated, for modal) ────────────────────────────────────

add_action( 'wp_ajax_wph_cap_get_logs_ajax', 'wph_cap_ajax_get_logs' );
function wph_cap_ajax_get_logs() {
    check_ajax_referer( 'wph_cap_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $page = max( 1, absint( $_POST['page'] ?? 1 ) );
    $data = wph_cap_get_logs( array( 'per_page' => 20, 'page' => $page ) );
    wp_send_json_success( $data );
}

// ─── AJAX: clear captcha logs ─────────────────────────────────────────────────

add_action( 'wp_ajax_wph_cap_clear_logs', 'wph_cap_ajax_clear_logs' );
function wph_cap_ajax_clear_logs() {
    check_ajax_referer( 'wph_cap_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    global $wpdb;
    $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wph_captcha_logs" );
    wp_send_json_success( array( 'message' => 'Đã xóa nhật ký' ) );
}

// ─── Log settings: save + daily cleanup ───────────────────────────────────────

add_action( 'wp_ajax_wph_cap_save_log_settings', 'wph_cap_ajax_save_log_settings' );
function wph_cap_ajax_save_log_settings() {
    check_ajax_referer( 'wph_cap_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    update_option( 'wph_captcha_log_settings', array(
        'retention' => absint( $_POST['retention'] ?? 0 ),
        'max_logs'  => absint( $_POST['max_logs']  ?? 0 ),
    ) );
    wp_send_json_success( array( 'message' => 'Đã lưu cài đặt lưu log' ) );
}

function wph_cap_cleanup_logs() {
    global $wpdb;
    $s   = get_option( 'wph_captcha_log_settings', array() );
    $r   = absint( $s['retention'] ?? 0 );
    if ( $r > 0 ) {
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wph_captcha_logs WHERE created_at < %s", date( 'Y-m-d H:i:s', strtotime( "-{$r} days" ) ) ) );
    }
    $max = absint( $s['max_logs'] ?? 0 );
    if ( $max > 0 ) {
        $min_id = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(id) FROM (SELECT id FROM {$wpdb->prefix}wph_captcha_logs ORDER BY id DESC LIMIT %d) t", $max ) );
        if ( $min_id ) {
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wph_captcha_logs WHERE id < %d", $min_id ) );
        }
    }
}
add_action( 'wph_cap_daily_cleanup', 'wph_cap_cleanup_logs' );
if ( ! wp_next_scheduled( 'wph_cap_daily_cleanup' ) ) {
    wp_schedule_event( time(), 'daily', 'wph_cap_daily_cleanup' );
}
