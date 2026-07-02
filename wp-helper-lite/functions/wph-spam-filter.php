<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── DB: spam log table ───────────────────────────────────────────────────────

function wph_sf_create_table() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$wpdb->prefix}wph_spam_logs (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        ip_address varchar(100) NOT NULL DEFAULT '',
        email varchar(255) NOT NULL DEFAULT '',
        reason varchar(255) NOT NULL DEFAULT '',
        form_plugin varchar(100) NOT NULL DEFAULT '',
        country varchar(5) NOT NULL DEFAULT '',
        status varchar(20) NOT NULL DEFAULT 'blocked',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY created_at (created_at),
        KEY ip_address (ip_address(20))
    ) {$charset};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'init', 'wph_sf_create_table' );

function wph_sf_log( $ip, $email, $reason, $plugin = '', $status = 'blocked' ) {
    global $wpdb;
    $status  = in_array( $status, array( 'blocked', 'whitelisted', 'monitor' ), true ) ? $status : 'blocked';
    $country = wph_sf_get_country( $ip );
    $wpdb->insert( $wpdb->prefix . 'wph_spam_logs', array(
        'ip_address'  => substr( $ip, 0, 100 ),
        'email'       => substr( sanitize_email( $email ), 0, 255 ),
        'reason'      => substr( $reason, 0, 255 ),
        'form_plugin' => substr( $plugin, 0, 100 ),
        'country'     => substr( $country ?: '', 0, 5 ),
        'status'      => $status,
        'created_at'  => current_time( 'mysql' ),
    ) );
    return (int) $wpdb->insert_id;
}

function wph_sf_get_logs( $args = array() ) {
    global $wpdb;
    $per_page = absint( $args['per_page'] ?? 20 );
    $page     = max( 1, absint( $args['page'] ?? 1 ) );
    $offset   = ( $page - 1 ) * $per_page;
    $total    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_spam_logs" );
    $rows     = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wph_spam_logs ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ) );
    return array( 'total' => $total, 'rows' => $rows ?: array() );
}

function wph_sf_get_stats() {
    global $wpdb;
    $table = $wpdb->prefix . 'wph_spam_logs';
    return array(
        'today'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at)=%s", current_time( 'Y-m-d' ) ) ),
        'week'    => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE created_at >= %s", date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ) ) ),
        'by_ip'   => (int) $wpdb->get_var( "SELECT COUNT(DISTINCT ip_address) FROM {$table}" ),
        'by_email'=> (int) $wpdb->get_var( "SELECT COUNT(DISTINCT email) FROM {$table} WHERE email != ''" ),
    );
}

// ─── SETTINGS ────────────────────────────────────────────────────────────────

function wph_sf_settings() {
    return get_option( 'wph_spam_filter_settings', array() );
}

// ─── MASTER SPAM CHECK ───────────────────────────────────────────────────────
// Returns array('spam'=>bool, 'reason'=>string) or false

function wph_sf_check( $posted_data = array(), $plugin = 'cf7' ) {
    $s = wph_sf_settings();

    // Master toggle: if disabled, skip all checks
    if ( empty( $s['active'] ) || $s['active'] !== '1' ) {
        return array( 'spam' => false, 'reason' => '' );
    }

    $monitor    = ! empty( $s['monitor_mode'] ) && $s['monitor_mode'] === '1';
    $log_status = $monitor ? 'monitor' : 'blocked';
    $ip         = wph_fm_get_ip();
    $ua         = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Extract email from posted data
    $email = '';
    foreach ( $posted_data as $k => $v ) {
        if ( strpos( strtolower($k), 'email' ) !== false && filter_var( $v, FILTER_VALIDATE_EMAIL ) ) {
            $email = $v; break;
        }
    }

    // IP whitelist — always allow, skip all checks
    if ( ! empty( $s['ip_block']['whitelist'] ) ) {
        $wl = array_filter( array_map( 'trim', explode( "\n", $s['ip_block']['whitelist'] ) ) );
        if ( in_array( $ip, $wl, true ) ) {
            return array( 'spam' => false, 'reason' => '' );
        }
    }

    // 1. Honeypot
    if ( ! empty( $s['honeypot']['active'] ) && $s['honeypot']['active'] === '1' ) {
        $hp_field = $s['honeypot']['field'] ?? 'wph_hp_field';
        if ( ! empty( $_POST[ $hp_field ] ) ) {
            wph_sf_log( $ip, $email, 'Honeypot triggered', $plugin, $log_status );
            return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : 'Honeypot triggered' );
        }
    }

    // 2. Rate limiting
    if ( ! empty( $s['rate_limit']['active'] ) && $s['rate_limit']['active'] === '1' ) {
        $max     = absint( $s['rate_limit']['max'] ?? 3 );
        $minutes = absint( $s['rate_limit']['minutes'] ?? 5 );
        $key     = 'wph_rl_' . md5( $ip );
        $count   = (int) get_transient( $key );
        if ( $count >= $max ) {
            wph_sf_log( $ip, $email, "Rate limit: {$count}/{$max} in {$minutes}min", $plugin, $log_status );
            return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : "Quá nhiều lần gửi ({$count}/{$max})" );
        }
        set_transient( $key, $count + 1, $minutes * 60 );
    }

    // 3. IP blacklist
    if ( ! empty( $s['ip_block']['blacklist'] ) ) {
        $bl = array_filter( array_map( 'trim', explode( "\n", $s['ip_block']['blacklist'] ) ) );
        if ( in_array( $ip, $bl, true ) ) {
            wph_sf_log( $ip, $email, 'IP blacklisted', $plugin, $log_status );
            return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : 'IP blocked' );
        }
    }

    // 5. Country block
    if ( ! empty( $s['country_block']['active'] ) && $s['country_block']['active'] === '1' ) {
        $blocked_countries = $s['country_block']['countries'] ?? array();
        if ( ! empty( $blocked_countries ) ) {
            $country = wph_sf_get_country( $ip );
            if ( $country && in_array( $country, $blocked_countries, true ) ) {
                wph_sf_log( $ip, $email, "Country blocked: {$country}", $plugin, $log_status );
                return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : "Country blocked: {$country}" );
            }
        }
    }

    // 6. Email blacklist
    if ( $email ) {
        $email_list = array_filter( array_map( 'trim', explode( "\n", $s['email_block']['emails'] ?? '' ) ) );
        if ( in_array( strtolower( $email ), array_map( 'strtolower', $email_list ), true ) ) {
            wph_sf_log( $ip, $email, 'Email blacklisted', $plugin, $log_status );
            return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : 'Email blocked' );
        }

        // Domain blacklist
        $domain       = substr( strrchr( $email, '@' ), 1 );
        $domain_list  = array_filter( array_map( 'trim', explode( "\n", $s['email_block']['domains'] ?? '' ) ) );
        if ( $domain && in_array( strtolower( $domain ), array_map( 'strtolower', $domain_list ), true ) ) {
            wph_sf_log( $ip, $email, "Domain blacklisted: {$domain}", $plugin, $log_status );
            return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : "Email domain blocked: {$domain}" );
        }

        // Temporary email check
        if ( ! empty( $s['email_block']['temp_active'] ) && $s['email_block']['temp_active'] === '1' ) {
            $temp_domains = wph_sf_temp_email_domains();
            if ( in_array( strtolower( $domain ), $temp_domains, true ) ) {
                wph_sf_log( $ip, $email, "Temp email: {$domain}", $plugin, $log_status );
                return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : 'Temporary email not allowed' );
            }
        }
    }

    // 7. Keyword check
    if ( ! empty( $s['keyword_block']['list'] ) ) {
        $keywords   = array_filter( array_map( 'trim', explode( "\n", $s['keyword_block']['list'] ) ) );
        $full_text  = strtolower( implode( ' ', array_map( function($v){ return is_array($v) ? implode(' ',$v) : $v; }, $posted_data ) ) );
        foreach ( $keywords as $kw ) {
            if ( $kw && strpos( $full_text, strtolower( $kw ) ) !== false ) {
                wph_sf_log( $ip, $email, "Keyword: {$kw}", $plugin, $log_status );
                return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : "Spam keyword: {$kw}" );
            }
        }
    }

    // 8. Code / script injection detection
    if ( ! empty( $s['code_detect']['active'] ) && $s['code_detect']['active'] === '1' ) {
        $level     = $s['code_detect']['level'] ?? 'basic';
        $raw_text  = implode( ' ', array_map( function( $v ) { return is_array( $v ) ? implode( ' ', $v ) : (string) $v; }, $posted_data ) );
        $detected  = wph_sf_detect_code( $raw_text, $level );
        if ( $detected ) {
            wph_sf_log( $ip, $email, "Code injection: {$detected}", $plugin, $log_status );
            return array( 'spam' => ! $monitor, 'reason' => $monitor ? '' : "Code injection: {$detected}" );
        }
    }

    return array( 'spam' => false, 'reason' => '' );
}

// ─── CODE INJECTION DETECTOR ─────────────────────────────────────────────────

function wph_sf_detect_code( $text, $level = 'basic' ) {
    // HTML/script injection — both levels
    if ( preg_match( '/<\s*(script|iframe|object|embed|svg|base)[^>]*>/i', $text ) ) return 'HTML injection';
    // SQL injection — both levels
    if ( preg_match( '/\b(SELECT|INSERT|UPDATE|DELETE|DROP|UNION|EXEC|CAST)\b.{0,40}\b(FROM|INTO|WHERE|TABLE)\b/is', $text ) ) return 'SQL injection';
    // JSON payload — both levels: detect {"key": ... pattern
    if ( preg_match( '/\{\s*"[a-zA-Z_][^"]{0,60}"\s*\s*:/s', $text ) ) return 'JSON payload';

    if ( $level === 'strong' ) {
        // URL flooding: 3+ URLs
        if ( preg_match_all( '/https?:\/\/[^\s"\'<>]{10,}/', $text, $m ) && count( $m[0] ) >= 3 ) return 'URL flooding';
        // Base64 blob longer than 60 chars
        if ( preg_match( '/[A-Za-z0-9+\/]{60,}={0,2}/', $text ) ) return 'Base64 payload';
    }

    return false;
}

// ─── CF7 INTEGRATION ─────────────────────────────────────────────────────────

add_filter( 'wpcf7_spam', 'wph_sf_check_cf7', 10 );
function wph_sf_check_cf7( $is_spam ) {
    if ( $is_spam ) return $is_spam;
    $submission = WPCF7_Submission::get_instance();
    $posted     = $submission ? $submission->get_posted_data() : array();
    $result     = wph_sf_check( $posted, 'cf7' );
    return $result['spam'] ? true : $is_spam;
}

// ─── WPFORMS INTEGRATION ─────────────────────────────────────────────────────

add_action( 'wpforms_process', 'wph_sf_check_wpforms', 5, 3 );
function wph_sf_check_wpforms( $fields, $entry, $form_data ) {
    $posted = array();
    foreach ( $fields as $field ) {
        if ( isset( $field['value'] ) ) $posted[] = $field['value'];
    }
    $result = wph_sf_check( $posted, 'wpforms' );
    if ( $result['spam'] ) {
        wpforms()->get( 'process' )->errors[ $form_data['id'] ]['footer'] = 'Phát hiện nội dung không hợp lệ. Vui lòng kiểm tra lại nội dung gửi.';
    }
}

// ─── WORDPRESS COMMENTS INTEGRATION ──────────────────────────────────────────

add_filter( 'preprocess_comment', 'wph_sf_check_comment' );
function wph_sf_check_comment( $commentdata ) {
    $posted = array(
        'comment_content' => $commentdata['comment_content'] ?? '',
        'comment_author'  => $commentdata['comment_author'] ?? '',
        'comment_author_url' => $commentdata['comment_author_url'] ?? '',
    );
    $result = wph_sf_check( $posted, 'comment' );
    if ( $result['spam'] ) {
        wp_die(
            esc_html( 'Bình luận bị từ chối: ' . $result['reason'] ),
            'Không hợp lệ',
            array( 'response' => 403, 'back_link' => true )
        );
    }
    return $commentdata;
}

// ─── HONEYPOT FIELD INJECTION ────────────────────────────────────────────────

add_filter( 'wpcf7_form_elements', 'wph_sf_inject_honeypot_cf7' );
function wph_sf_inject_honeypot_cf7( $html ) {
    $s = wph_sf_settings();
    if ( empty( $s['active'] ) || $s['active'] !== '1' ) return $html;
    if ( empty( $s['honeypot']['active'] ) || $s['honeypot']['active'] !== '1' ) return $html;
    $field = esc_attr( $s['honeypot']['field'] ?? 'wph_hp_field' );
    $hp    = '<div style="display:none!important;visibility:hidden;position:absolute;left:-9999px;" aria-hidden="true">'
           . '<input type="text" name="' . $field . '" value="" tabindex="-1" autocomplete="off">'
           . '</div>';
    return $hp . $html;
}

// ─── COUNTRY LOOKUP (ip-api.com free) ────────────────────────────────────────

function wph_sf_get_country( $ip ) {
    // Cloudflare header (instant, no API call)
    if ( ! empty( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
        return strtoupper( $_SERVER['HTTP_CF_IPCOUNTRY'] );
    }
    // Cache per IP to avoid repeated API calls
    $cache_key = 'wph_geo_' . md5( $ip );
    $cached    = get_transient( $cache_key );
    if ( $cached !== false ) return $cached;

    $response = wp_remote_get( "http://ip-api.com/json/{$ip}?fields=countryCode", array(
        'timeout'   => 5,
        'sslverify' => true,
    ) );
    if ( is_wp_error( $response ) ) { set_transient( $cache_key, '', 3600 ); return ''; }
    $data    = json_decode( wp_remote_retrieve_body( $response ), true );
    $country = $data['countryCode'] ?? '';
    set_transient( $cache_key, $country, 86400 ); // cache 24h
    return $country;
}

// ─── TEMP EMAIL DOMAINS ───────────────────────────────────────────────────────

function wph_sf_temp_email_domains() {
    return array(
        'mailinator.com','10minutemail.com','guerrillamail.com','throwaway.email',
        'tempmail.com','fakeinbox.com','yopmail.com','sharklasers.com','guerrillamailblock.com',
        'grr.la','guerrillamail.info','guerrillamail.biz','guerrillamail.de','guerrillamail.net',
        'guerrillamail.org','spam4.me','trashmail.com','trashmail.me','trashmail.net',
        'dispostable.com','mailnull.com','spamgourmet.com','spamgourmet.net','spamgourmet.org',
        'wegwerfemail.de','sofort-mail.de','emailondeck.com','throwam.com','getnada.com',
        'maildrop.cc','discard.email','spamex.com','safetymail.info','anonaddy.com',
        'mohmal.com','getairmail.com','filzmail.com','einrot.com','dingbone.com',
        'flocktail.com','comsafe-mail.net','email-fake.com','temp-mail.org','tempr.email',
        'mailtemporaire.fr','fakemailgenerator.com','anonbox.net','bspamfree.org',
    );
}

// ─── REASON STATS (for donut chart) ─────────────────────────────────────────

function wph_sf_get_reason_stats() {
    global $wpdb;
    $rows  = $wpdb->get_results( "SELECT reason, COUNT(*) as cnt FROM {$wpdb->prefix}wph_spam_logs GROUP BY reason" );

    $cats = array(
        'Honeypot'     => 0,
        'Rate Limit'   => 0,
        'Bot / UA'     => 0,
        'IP / Country' => 0,
        'Email'        => 0,
        'Keyword'      => 0,
        'Khác'         => 0,
    );
    foreach ( $rows as $row ) {
        $r   = strtolower( $row->reason );
        $cnt = (int) $row->cnt;
        if ( strpos( $r, 'honeypot' ) !== false ) {
            $cats['Honeypot'] += $cnt;
        } elseif ( strpos( $r, 'rate' ) !== false ) {
            $cats['Rate Limit'] += $cnt;
        } elseif ( strpos( $r, 'bot' ) !== false || strpos( $r, ' ua' ) !== false ) {
            $cats['Bot / UA'] += $cnt;
        } elseif ( strpos( $r, 'ip ' ) !== false || strpos( $r, 'country' ) !== false || strpos( $r, 'proxy' ) !== false || strpos( $r, 'blacklist' ) !== false ) {
            $cats['IP / Country'] += $cnt;
        } elseif ( strpos( $r, 'email' ) !== false || strpos( $r, 'domain' ) !== false || strpos( $r, 'temp' ) !== false ) {
            $cats['Email'] += $cnt;
        } elseif ( strpos( $r, 'keyword' ) !== false ) {
            $cats['Keyword'] += $cnt;
        } else {
            $cats['Khác'] += $cnt;
        }
    }
    return array_filter( $cats, function( $v ) { return $v > 0; } );
}

// ─── AJAX: save settings ─────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_sf_save_settings', 'wph_sf_ajax_save_settings' );
function wph_sf_ajax_save_settings() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $raw = json_decode( wp_unslash( $_POST['settings'] ?? '{}' ), true );
    if ( ! is_array( $raw ) ) $raw = array();

    // Merge with existing (preserves list fields managed by quick-block)
    $existing = get_option( 'wph_spam_filter_settings', array() );

    $settings = array_merge( $existing, array(
        'active'       => sanitize_text_field( $raw['active'] ?? '0' ),
        'monitor_mode' => sanitize_text_field( $raw['monitor_mode'] ?? '0' ),
        'dnsbl_level'  => sanitize_key( $raw['dnsbl_level'] ?? 'off' ),
        'hide_error'   => sanitize_text_field( $raw['hide_error'] ?? '0' ),
        'honeypot'   => array_merge( $existing['honeypot'] ?? array(), array(
            'active' => sanitize_text_field( $raw['honeypot']['active'] ?? '0' ),
            'field'  => sanitize_key( $existing['honeypot']['field'] ?? 'wph_hp_field' ),
        ) ),
        'rate_limit' => array(
            'active'  => sanitize_text_field( $raw['rate_limit']['active'] ?? '0' ),
            'max'     => absint( $raw['rate_limit']['max'] ?? 3 ),
            'minutes' => absint( $raw['rate_limit']['minutes'] ?? 5 ),
        ),
        'proxy_vpn'  => array(
            'active' => sanitize_text_field( $raw['proxy_vpn']['active'] ?? '0' ),
        ),
        'email_block'=> array_merge( $existing['email_block'] ?? array(), array(
            'temp_active' => sanitize_text_field( $raw['email_block']['temp_active'] ?? '0' ),
        ) ),
        'code_detect'=> array(
            'active' => sanitize_text_field( $raw['code_detect']['active'] ?? '0' ),
            'level'  => in_array( $raw['code_detect']['level'] ?? '', array( 'basic', 'strong' ), true ) ? $raw['code_detect']['level'] : 'basic',
        ),
    ) );
    update_option( 'wph_spam_filter_settings', $settings );
    wp_send_json_success( array( 'message' => 'Đã lưu cài đặt chống spam' ) );
}

// ─── AJAX: quick-block add/remove ─────────────────────────────────────────────

add_action( 'wp_ajax_wph_sf_quick_block_add', 'wph_sf_ajax_quick_block_add' );
function wph_sf_ajax_quick_block_add() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $type  = sanitize_key( $_POST['type'] ?? '' );
    $value = sanitize_text_field( $_POST['value'] ?? '' );
    if ( ! $type || ! $value ) wp_send_json_error( array( 'message' => 'Thiếu dữ liệu' ) );

    $settings = get_option( 'wph_spam_filter_settings', array() );

    switch ( $type ) {
        case 'ip':
            // Validate: bare IP or CIDR notation
            $ip_clean = trim( $value );
            if ( strpos( $ip_clean, '/' ) !== false ) {
                list( $ip_part, $prefix ) = explode( '/', $ip_clean, 2 );
                $valid_ip = filter_var( $ip_part, FILTER_VALIDATE_IP );
                $valid_prefix = ctype_digit( $prefix ) && (int) $prefix >= 0 && (int) $prefix <= 128;
                if ( ! $valid_ip || ! $valid_prefix ) {
                    wp_send_json_error( array( 'message' => 'Địa chỉ IP/CIDR không hợp lệ' ) );
                }
            } elseif ( ! filter_var( $ip_clean, FILTER_VALIDATE_IP ) ) {
                wp_send_json_error( array( 'message' => 'Địa chỉ IP không hợp lệ. Ví dụ: 192.168.1.1' ) );
            }
            $value = $ip_clean;
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['ip_block']['blacklist'] ?? '' ) ) );
            if ( ! in_array( $value, $list, true ) ) { $list[] = $value; }
            $settings['ip_block']['blacklist'] = implode( "\n", array_values( $list ) );
            break;
        case 'email':
            // Allow: full email OR domain/subdomain pattern (optionally prefixed with @)
            $email_clean = strtolower( trim( $value ) );
            $valid_email  = is_email( $email_clean );
            $valid_domain = (bool) preg_match( '/^@?[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/', $email_clean );
            if ( ! $valid_email && ! $valid_domain ) {
                wp_send_json_error( array( 'message' => 'Định dạng không hợp lệ. Ví dụ: spam@gmail.com hoặc @tempmail.com' ) );
            }
            $value = $email_clean;
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['email_block']['emails'] ?? '' ) ) );
            if ( ! in_array( $value, $list, true ) ) { $list[] = $value; }
            $settings['email_block']['emails'] = implode( "\n", array_values( $list ) );
            break;
        case 'domain':
            $dom_clean = strtolower( trim( $value ) );
            if ( ! preg_match( '/^@?[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z]{2,})+$/', $dom_clean ) ) {
                wp_send_json_error( array( 'message' => 'Tên miền không hợp lệ. Ví dụ: spam.com hoặc @tempmail.net' ) );
            }
            $value = $dom_clean;
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['email_block']['domains'] ?? '' ) ) );
            if ( ! in_array( $value, $list, true ) ) { $list[] = $value; }
            $settings['email_block']['domains'] = implode( "\n", array_values( $list ) );
            break;
        case 'keyword':
            // Reject code/JSON payloads
            if ( wph_sf_detect_code( $value, 'basic' ) ) {
                wp_send_json_error( array( 'message' => 'Từ khóa chứa code/JSON — không thể lưu' ) );
            }
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['keyword_block']['list'] ?? '' ) ) );
            if ( ! in_array( $value, $list, true ) ) { $list[] = $value; }
            $settings['keyword_block']['list'] = implode( "\n", array_values( $list ) );
            break;
        case 'country':
            $code = strtoupper( preg_replace( '/[^A-Za-z]/', '', $value ) );
            if ( strlen( $code ) !== 2 ) wp_send_json_error( array( 'message' => 'Mã quốc gia không hợp lệ' ) );
            $countries = $settings['country_block']['countries'] ?? array();
            if ( ! in_array( $code, $countries, true ) ) {
                $countries[] = $code;
                $settings['country_block']['countries'] = array_values( $countries );
                $settings['country_block']['active']    = '1';
            }
            $value = $code;
            break;
        default:
            wp_send_json_error( array( 'message' => 'Loại không hợp lệ' ) );
    }

    update_option( 'wph_spam_filter_settings', $settings );
    wp_send_json_success( array( 'message' => 'Đã thêm', 'value' => $value ) );
}

add_action( 'wp_ajax_wph_sf_quick_block_remove', 'wph_sf_ajax_quick_block_remove' );
function wph_sf_ajax_quick_block_remove() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $type  = sanitize_key( $_POST['type'] ?? '' );
    $value = sanitize_text_field( $_POST['value'] ?? '' );
    if ( ! $type || ! $value ) wp_send_json_error( array( 'message' => 'Thiếu dữ liệu' ) );

    $settings = get_option( 'wph_spam_filter_settings', array() );
    $filter   = function( $v ) use ( $value ) { return trim( $v ) !== $value; };

    switch ( $type ) {
        case 'ip':
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['ip_block']['blacklist'] ?? '' ) ), $filter );
            $settings['ip_block']['blacklist'] = implode( "\n", array_values( $list ) );
            break;
        case 'email':
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['email_block']['emails'] ?? '' ) ), $filter );
            $settings['email_block']['emails'] = implode( "\n", array_values( $list ) );
            break;
        case 'domain':
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['email_block']['domains'] ?? '' ) ), $filter );
            $settings['email_block']['domains'] = implode( "\n", array_values( $list ) );
            break;
        case 'keyword':
            $list = array_filter( array_map( 'trim', explode( "\n", $settings['keyword_block']['list'] ?? '' ) ), $filter );
            $settings['keyword_block']['list'] = implode( "\n", array_values( $list ) );
            break;
        case 'country':
            $code = strtoupper( preg_replace( '/[^A-Za-z]/', '', $value ) );
            $countries = array_filter( $settings['country_block']['countries'] ?? array(), function( $c ) use ( $code ) {
                return $c !== $code;
            } );
            $settings['country_block']['countries'] = array_values( $countries );
            if ( empty( $settings['country_block']['countries'] ) ) {
                $settings['country_block']['active'] = '0';
            }
            break;
        default:
            wp_send_json_error( array( 'message' => 'Loại không hợp lệ' ) );
    }

    update_option( 'wph_spam_filter_settings', $settings );
    wp_send_json_success( array( 'message' => 'Đã xóa' ) );
}

// ─── AJAX: bulk keyword import ───────────────────────────────────────────────

add_action( 'wp_ajax_wph_sf_bulk_keyword_import', 'wph_sf_ajax_bulk_keyword_import' );
function wph_sf_ajax_bulk_keyword_import() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $raw   = sanitize_textarea_field( wp_unslash( $_POST['keywords'] ?? '' ) );

    // Code/JSON gate — reject before splitting or saving
    $code_type = wph_sf_detect_code( $raw, 'basic' );
    if ( $code_type ) {
        wp_send_json_error( array( 'message' => 'Phát hiện nội dung code/JSON (' . $code_type . ') — không thể import từ khóa này' ) );
    }

    $lines = preg_split( '/[\n,]+/', $raw );
    $new_kws = array_values( array_filter( array_map( 'sanitize_text_field', array_map( 'trim', $lines ) ) ) );
    if ( empty( $new_kws ) ) {
        wp_send_json_error( array( 'message' => 'Không có từ khóa nào hợp lệ' ) );
    }

    $settings = get_option( 'wph_spam_filter_settings', array() );
    $existing = array_values( array_filter( array_map( 'trim', explode( "\n", $settings['keyword_block']['list'] ?? '' ) ) ) );
    $merged   = array_values( array_unique( array_merge( $existing, $new_kws ) ) );
    $added    = count( $merged ) - count( $existing );

    $settings['keyword_block']['list'] = implode( "\n", $merged );
    update_option( 'wph_spam_filter_settings', $settings );

    // Return only the newly added keywords (not duplicates)
    $truly_new = array_values( array_diff( $new_kws, $existing ) );
    wp_send_json_success( array( 'added' => $added, 'total' => count( $merged ), 'keywords' => $truly_new ) );
}

// ─── AJAX: filtered log query ─────────────────────────────────────────────────

add_action( 'wp_ajax_wph_sf_get_logs_ajax', 'wph_sf_ajax_get_logs_ajax' );
function wph_sf_ajax_get_logs_ajax() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    global $wpdb;
    $table    = $wpdb->prefix . 'wph_spam_logs';
    $per_page = 25;
    $page     = max( 1, absint( $_POST['page'] ?? 1 ) );
    $offset   = ( $page - 1 ) * $per_page;
    $search        = sanitize_text_field( $_POST['search'] ?? '' );
    $reason        = sanitize_text_field( $_POST['reason'] ?? '' );
    $status_filter = sanitize_key( $_POST['status'] ?? '' );
    $date_from     = sanitize_text_field( $_POST['date_from'] ?? '' );
    $date_to       = sanitize_text_field( $_POST['date_to'] ?? '' );

    $where  = array( '1=1' );
    $params = array();

    if ( $status_filter && in_array( $status_filter, array( 'blocked', 'whitelisted', 'monitor' ), true ) ) {
        $where[]  = 'status = %s';
        $params[] = $status_filter;
    }
    if ( $search ) {
        $like     = '%' . $wpdb->esc_like( $search ) . '%';
        $where[]  = '(ip_address LIKE %s OR email LIKE %s OR reason LIKE %s)';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if ( $reason ) {
        $like     = '%' . $wpdb->esc_like( $reason ) . '%';
        $where[]  = 'reason LIKE %s';
        $params[] = $like;
    }
    if ( $date_from && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_from ) ) {
        $where[]  = 'created_at >= %s';
        $params[] = $date_from . ' 00:00:00';
    }
    if ( $date_to && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_to ) ) {
        $where[]  = 'created_at <= %s';
        $params[] = $date_to . ' 23:59:59';
    }

    $where_sql = implode( ' AND ', $where );

    $total = empty( $params )
        ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}" )
        : (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $params ) );

    $data_sql    = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $data_params = array_merge( $params, array( $per_page, $offset ) );
    $rows        = $wpdb->get_results( $wpdb->prepare( $data_sql, $data_params ) );

    wp_send_json_success( array(
        'rows'         => $rows ?: array(),
        'total'        => $total,
        'pages'        => max( 1, ceil( $total / $per_page ) ),
        'current_page' => $page,
    ) );
}

// ─── AJAX: update log status (whitelist / reblock) ───────────────────────────

add_action( 'wp_ajax_wph_sf_update_log_status', 'wph_sf_ajax_update_log_status' );
function wph_sf_ajax_update_log_status() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    global $wpdb;
    $id         = absint( $_POST['id'] ?? 0 );
    $new_status = sanitize_key( $_POST['new_status'] ?? '' );
    $add_to_wl  = ! empty( $_POST['add_to_whitelist'] ) && $_POST['add_to_whitelist'] === '1';

    if ( ! $id || ! in_array( $new_status, array( 'blocked', 'whitelisted', 'monitor' ), true ) ) {
        wp_send_json_error( array( 'message' => 'Dữ liệu không hợp lệ' ) );
    }

    $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wph_spam_logs WHERE id = %d", $id ) );
    if ( ! $row ) wp_send_json_error( array( 'message' => 'Không tìm thấy bản ghi' ) );

    $wpdb->update( $wpdb->prefix . 'wph_spam_logs', array( 'status' => $new_status ), array( 'id' => $id ) );

    // Add IP to whitelist in settings
    if ( $add_to_wl && $new_status === 'whitelisted' && $row->ip_address ) {
        $settings = get_option( 'wph_spam_filter_settings', array() );
        $wl = array_filter( array_map( 'trim', explode( "\n", $settings['ip_block']['whitelist'] ?? '' ) ) );
        if ( ! in_array( $row->ip_address, $wl, true ) ) {
            $wl[] = $row->ip_address;
            $settings['ip_block']['whitelist'] = implode( "\n", $wl );
            update_option( 'wph_spam_filter_settings', $settings );
        }
    }

    wp_send_json_success( array( 'message' => 'Đã cập nhật trạng thái', 'new_status' => $new_status ) );
}

// ─── AJAX: export logs CSV ────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_sf_export_logs', 'wph_sf_ajax_export_logs' );
function wph_sf_ajax_export_logs() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    global $wpdb;
    $rows  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wph_spam_logs ORDER BY created_at DESC LIMIT 5000" );
    $lines = array( '"IP","Email","Lý do","Plugin","Thời gian"' );
    foreach ( $rows as $r ) {
        $lines[] = implode( ',', array_map( function( $v ) {
            return '"' . str_replace( '"', '""', $v ) . '"';
        }, array( $r->ip_address, $r->email, $r->reason, $r->form_plugin, $r->created_at ) ) );
    }
    wp_send_json_success( array( 'csv' => implode( "\n", $lines ) ) );
}

// ─── AJAX: bulk delete spam logs ─────────────────────────────────────────────

add_action( 'wp_ajax_wph_sf_bulk_delete', 'wph_sf_ajax_bulk_delete' );
function wph_sf_ajax_bulk_delete() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $ids = array_filter( array_map( 'absint', (array) ( $_POST['ids'] ?? array() ) ) );
    if ( empty( $ids ) ) {
        wp_send_json_error( array( 'message' => 'Không có bản ghi nào được chọn' ) );
        return;
    }
    global $wpdb;
    $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wph_spam_logs WHERE id IN ($placeholders)", $ids ) );
    wp_send_json_success( array( 'message' => sprintf( 'Đã xóa %d bản ghi', count( $ids ) ) ) );
}

// ─── AJAX: clear spam logs ────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_sf_clear_logs', 'wph_sf_ajax_clear_logs' );
function wph_sf_ajax_clear_logs() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    global $wpdb;
    $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wph_spam_logs" );
    wp_send_json_success( array( 'message' => 'Đã xóa nhật ký spam' ) );
}

// ─── AJAX: Email log AJAX ─────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_el_delete', 'wph_el_ajax_delete' );
function wph_el_ajax_delete() {
    check_ajax_referer( 'wph_el_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $id = absint( $_POST['id'] ?? 0 );
    if ( $id ) wph_el_delete( $id );
    wp_send_json_success( array( 'message' => 'Đã xóa' ) );
}

add_action( 'wp_ajax_wph_el_delete_all', 'wph_el_ajax_delete_all' );
function wph_el_ajax_delete_all() {
    check_ajax_referer( 'wph_el_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $status = sanitize_key( $_POST['status'] ?? '' );
    wph_el_delete_all( $status );
    wp_send_json_success( array( 'message' => 'Đã xóa' ) );
}

add_action( 'wp_ajax_wph_el_resend', 'wph_el_ajax_resend' );
function wph_el_ajax_resend() {
    check_ajax_referer( 'wph_el_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $id     = absint( $_POST['id'] ?? 0 );
    $result = wph_el_resend( $id );
    if ( is_wp_error( $result ) ) {
        wp_send_json_error( array( 'message' => $result->get_error_message() ) );
    } else {
        wp_send_json_success( array( 'message' => $result ? 'Đã gửi lại email' : 'Gửi lại thất bại' ) );
    }
}

add_action( 'wp_ajax_wph_el_save_settings', 'wph_el_ajax_save_settings' );
function wph_el_ajax_save_settings() {
    check_ajax_referer( 'wph_el_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $max_logs = absint( $_POST['max_logs'] ?? 50000 );
    $settings = array(
        'active'    => sanitize_text_field( $_POST['active'] ?? '1' ),
        'retention' => absint( $_POST['retention'] ?? 0 ),
        'max_logs'  => $max_logs,
    );
    update_option( 'wph_el_settings', $settings );
    wp_send_json_success( array( 'message' => 'Đã lưu cài đặt' ) );
}

// ─── Log settings: save + daily cleanup ───────────────────────────────────────

add_action( 'wp_ajax_wph_sf_save_log_settings', 'wph_sf_ajax_save_log_settings' );
function wph_sf_ajax_save_log_settings() {
    check_ajax_referer( 'wph_sf_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    update_option( 'wph_spam_log_settings', array(
        'retention' => absint( $_POST['retention'] ?? 0 ),
        'max_logs'  => absint( $_POST['max_logs']  ?? 0 ),
    ) );
    wp_send_json_success( array( 'message' => 'Đã lưu cài đặt lưu log' ) );
}

function wph_sf_cleanup_logs() {
    global $wpdb;
    $s   = get_option( 'wph_spam_log_settings', array() );
    $r   = absint( $s['retention'] ?? 0 );
    if ( $r > 0 ) {
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wph_spam_logs WHERE created_at < %s", date( 'Y-m-d H:i:s', strtotime( "-{$r} days" ) ) ) );
    }
    $max = absint( $s['max_logs'] ?? 0 );
    if ( $max > 0 ) {
        $min_id = $wpdb->get_var( $wpdb->prepare( "SELECT MIN(id) FROM (SELECT id FROM {$wpdb->prefix}wph_spam_logs ORDER BY id DESC LIMIT %d) t", $max ) );
        if ( $min_id ) {
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wph_spam_logs WHERE id < %d", $min_id ) );
        }
    }
}
add_action( 'wph_sf_daily_cleanup', 'wph_sf_cleanup_logs' );
if ( ! wp_next_scheduled( 'wph_sf_daily_cleanup' ) ) {
    wp_schedule_event( time(), 'daily', 'wph_sf_daily_cleanup' );
}
