<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── SMTP STATUS DETECTOR ─────────────────────────────────────────────────────
// Detects SMTP from built-in plugin settings + popular 3rd-party SMTP plugins.
// Returns: [ 'active'=>bool, 'source'=>string, 'host'=>string, 'port'=>string,
//            'security'=>string, 'from'=>string, 'from_name'=>string ]

function wph_el_detect_smtp() {
    // 1. Built-in WP Helper Lite SMTP (whp_setting option array)
    if ( function_exists( 'whp_get_option' ) ) {
        $active = whp_get_option( 'whp_smtp_active' );
        $host   = (string) whp_get_option( 'whp_smtp_host' );
        if ( $active == '1' && $host !== '' && $host !== '0' ) {
            return array(
                'active'    => true,
                'source'    => 'WP Helper Lite',
                'host'      => $host,
                'port'      => (string) whp_get_option( 'whp_smtp_port' ),
                'security'  => strtoupper( (string) whp_get_option( 'whp_smtp_security' ) ),
                'from'      => (string) whp_get_option( 'whp_smtp_email' ),
                'from_name' => (string) whp_get_option( 'whp_smtp_from_name' ),
            );
        }
    }

    // 2. WP Mail SMTP (by WPForms) — option: wp_mail_smtp
    $wms = get_option( 'wp_mail_smtp', array() );
    if ( ! empty( $wms['mail']['mailer'] ) && $wms['mail']['mailer'] !== 'mail' ) {
        $mailer  = $wms['mail']['mailer'];
        $smtp_s  = $wms['smtp'] ?? array();
        $mail_s  = $wms['mail'] ?? array();
        $host    = $smtp_s['host'] ?? '';
        if ( $mailer === 'smtp' && $host ) {
            return array(
                'active'    => true,
                'source'    => 'WP Mail SMTP',
                'host'      => $host,
                'port'      => (string) ( $smtp_s['port'] ?? '' ),
                'security'  => strtoupper( $smtp_s['encryption'] ?? '' ),
                'from'      => $mail_s['from_email'] ?? '',
                'from_name' => $mail_s['from_name'] ?? '',
            );
        }
        // Other mailers (sendgrid, mailgun, gmail, etc.)
        $label_map = array(
            'sendgrid' => 'SendGrid',
            'mailgun'  => 'Mailgun',
            'gmail'    => 'Gmail API',
            'outlook'  => 'Outlook',
            'zoho'     => 'Zoho Mail',
            'sparkpost' => 'SparkPost',
            'sendinblue' => 'Brevo (Sendinblue)',
            'amazon_ses' => 'Amazon SES',
        );
        return array(
            'active'    => true,
            'source'    => 'WP Mail SMTP (' . ( $label_map[ $mailer ] ?? ucfirst( $mailer ) ) . ')',
            'host'      => $label_map[ $mailer ] ?? ucfirst( $mailer ),
            'port'      => '',
            'security'  => '',
            'from'      => $mail_s['from_email'] ?? '',
            'from_name' => $mail_s['from_name'] ?? '',
        );
    }

    // 3. Post SMTP (Postman SMTP) — option: postman_options
    $ps = get_option( 'postman_options', array() );
    if ( ! empty( $ps['transport_type'] ) && $ps['transport_type'] !== 'default' ) {
        $host = $ps['hostname'] ?? ( $ps['smtp_hostname'] ?? '' );
        if ( $host ) {
            return array(
                'active'    => true,
                'source'    => 'Post SMTP',
                'host'      => $host,
                'port'      => (string) ( $ps['port'] ?? $ps['smtp_port'] ?? '' ),
                'security'  => strtoupper( $ps['enc_type'] ?? $ps['encryption'] ?? '' ),
                'from'      => $ps['sender_email'] ?? '',
                'from_name' => $ps['sender_name'] ?? '',
            );
        }
    }

    // 4. Fluent SMTP — option: _fluentsmtp_settings
    $fs = get_option( '_fluentsmtp_settings', array() );
    if ( ! empty( $fs['connections'] ) && is_array( $fs['connections'] ) ) {
        $conn = reset( $fs['connections'] );
        if ( ! empty( $conn['driver'] ) && $conn['driver'] !== 'default' ) {
            $host = $conn['host'] ?? ( $conn['sender_email'] ?? '' );
            return array(
                'active'    => true,
                'source'    => 'FluentSMTP',
                'host'      => $host,
                'port'      => (string) ( $conn['port'] ?? '' ),
                'security'  => strtoupper( $conn['encryption'] ?? '' ),
                'from'      => $conn['sender_email'] ?? '',
                'from_name' => $conn['sender_name'] ?? '',
            );
        }
    }

    // 5. Easy WP SMTP — option: swpsmtp_options
    $ews = get_option( 'swpsmtp_options', array() );
    $ews_smtp = $ews['smtp_settings'] ?? array();
    if ( ! empty( $ews_smtp['host'] ) ) {
        return array(
            'active'    => true,
            'source'    => 'Easy WP SMTP',
            'host'      => $ews_smtp['host'],
            'port'      => (string) ( $ews_smtp['port'] ?? '' ),
            'security'  => strtoupper( $ews_smtp['type_encryption'] ?? $ews_smtp['encryption'] ?? '' ),
            'from'      => $ews['from_email_field'] ?? '',
            'from_name' => $ews['from_name_field'] ?? '',
        );
    }

    // 6. SMTP Mailer — option: smtp_mailer_options
    $sm = get_option( 'smtp_mailer_options', array() );
    if ( ! empty( $sm['smtphost'] ) ) {
        return array(
            'active'    => true,
            'source'    => 'SMTP Mailer',
            'host'      => $sm['smtphost'],
            'port'      => (string) ( $sm['smtpport'] ?? '' ),
            'security'  => strtoupper( $sm['smtpssl'] ?? '' ),
            'from'      => $sm['smtpfrom'] ?? '',
            'from_name' => $sm['smtpfromname'] ?? '',
        );
    }

    // 7. Gmail SMTP by BTE — option: gmail_smtp_settings
    $gs = get_option( 'gmail_smtp_settings', array() );
    if ( ! empty( $gs['client_id'] ) || ! empty( $gs['oauth_access_token'] ) ) {
        return array(
            'active'    => true,
            'source'    => 'Gmail SMTP',
            'host'      => 'smtp.gmail.com',
            'port'      => '587',
            'security'  => 'TLS',
            'from'      => $gs['sender_email'] ?? '',
            'from_name' => $gs['sender_name'] ?? '',
        );
    }

    // 8. MailPoet (detects via PHPMailer hook for SMTP)
    $mp = get_option( 'mailpoet_settings', array() );
    if ( isset( $mp['mta'] ) && is_array( $mp['mta'] ) && ! empty( $mp['mta']['host'] ) ) {
        return array(
            'active'    => true,
            'source'    => 'MailPoet SMTP',
            'host'      => $mp['mta']['host'],
            'port'      => (string) ( $mp['mta']['port'] ?? '' ),
            'security'  => strtoupper( $mp['mta']['encryption'] ?? '' ),
            'from'      => $mp['sender']['address'] ?? '',
            'from_name' => $mp['sender']['name'] ?? '',
        );
    }

    // 9. Check phpmailer globals (some plugins configure via action hook only)
    // If any phpmailer Host is set at class level via wp_mail action, we can't detect statically.
    // Fall through to "not configured" state.

    return array(
        'active'    => false,
        'source'    => '',
        'host'      => '',
        'port'      => '',
        'security'  => '',
        'from'      => '',
        'from_name' => '',
    );
}

// ─── DB TABLE ────────────────────────────────────────────────────────────────

function wph_el_create_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'wph_email_logs';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        to_email varchar(500) NOT NULL DEFAULT '',
        subject varchar(500) NOT NULL DEFAULT '',
        message longtext NOT NULL,
        headers text NOT NULL,
        attachments text NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'sent',
        smtp_response text NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY created_at (created_at)
    ) {$charset};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'init', 'wph_el_create_table' );

// ─── HOOK wp_mail ────────────────────────────────────────────────────────────

// Default: logging is ACTIVE unless explicitly disabled (active === '0').
// This means emails are logged even before the user saves the log settings UI.
add_filter( 'wp_mail', 'wph_el_capture_mail', 9999 );
function wph_el_capture_mail( $args ) {
    $settings = get_option( 'wph_el_settings', array() );
    // Only skip if the user explicitly turned it off
    if ( isset( $settings['active'] ) && $settings['active'] === '0' ) return $args;

    global $wph_el_pending;
    $wph_el_pending = array(
        'to_email'    => is_array( $args['to'] ) ? implode( ', ', $args['to'] ) : (string) $args['to'],
        'subject'     => $args['subject'] ?? '',
        'message'     => $args['message'] ?? '',
        'headers'     => is_array( $args['headers'] ?? '' ) ? implode( "\n", $args['headers'] ) : ( $args['headers'] ?? '' ),
        'attachments' => is_array( $args['attachments'] ?? '' ) ? implode( ', ', $args['attachments'] ) : ( $args['attachments'] ?? '' ),
    );
    return $args;
}

// WP 5.9+: log success
add_action( 'wp_mail_succeeded', 'wph_el_on_succeeded' );
function wph_el_on_succeeded( $mail_data ) {
    global $wph_el_pending;
    if ( ! $wph_el_pending ) return;
    wph_el_insert( array_merge( $wph_el_pending, array( 'status' => 'sent', 'smtp_response' => '250 OK' ) ) );
    $wph_el_pending = null;
}

// WP 4.4+: log failure
add_action( 'wp_mail_failed', 'wph_el_on_failed' );
function wph_el_on_failed( $wp_error ) {
    global $wph_el_pending;
    if ( ! $wph_el_pending ) return;
    $msg = $wp_error instanceof WP_Error ? $wp_error->get_error_message() : 'Send failed';
    wph_el_insert( array_merge( $wph_el_pending, array( 'status' => 'failed', 'smtp_response' => $msg ) ) );
    $wph_el_pending = null;
}

// Fallback for WP < 5.9: wp_mail_succeeded doesn't exist, so use PHPMailer's
// action_function to catch successful sends. When wp_mail_succeeded fires (WP 5.9+),
// it sets $wph_el_pending=null — the PHPMailer callback then finds nothing to do.
add_action( 'phpmailer_init', 'wph_el_phpmailer_hook', 100 );
function wph_el_phpmailer_hook( $phpmailer ) {
    $prev = $phpmailer->action_function;
    $phpmailer->action_function = function( $isSent ) use ( $prev ) {
        if ( $isSent ) {
            global $wph_el_pending;
            // Only log here if wp_mail_succeeded hasn't already handled it
            if ( $wph_el_pending ) {
                wph_el_insert( array_merge( $wph_el_pending, array(
                    'status'        => 'sent',
                    'smtp_response' => '250 OK',
                ) ) );
                $wph_el_pending = null;
            }
        }
        if ( is_callable( $prev ) ) {
            call_user_func_array( $prev, func_get_args() );
        }
    };
}

// ─── CRUD ────────────────────────────────────────────────────────────────────

function wph_el_insert( $data ) {
    global $wpdb;
    $wpdb->insert( $wpdb->prefix . 'wph_email_logs', array(
        'to_email'     => substr( sanitize_text_field( $data['to_email'] ), 0, 500 ),
        'subject'      => substr( sanitize_text_field( $data['subject'] ), 0, 500 ),
        'message'      => $data['message'],
        'headers'      => $data['headers'],
        'attachments'  => $data['attachments'],
        'status'       => in_array( $data['status'], array( 'sent', 'failed' ), true ) ? $data['status'] : 'sent',
        'smtp_response'=> $data['smtp_response'],
        'created_at'   => current_time( 'mysql' ),
    ) );
    return $wpdb->insert_id;
}

function wph_el_get_logs( $args = array() ) {
    global $wpdb;
    $table    = $wpdb->prefix . 'wph_email_logs';
    $defaults = array(
        'status'     => '',
        'search'     => '',
        'date_range' => '',
        'per_page'   => 20,
        'page'       => 1,
    );
    $args   = wp_parse_args( $args, $defaults );
    $where  = array( '1=1' );
    $params = array();

    if ( ! empty( $args['status'] ) ) {
        $where[]  = 'status = %s';
        $params[] = $args['status'];
    }
    if ( ! empty( $args['search'] ) ) {
        $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where[]  = '(to_email LIKE %s OR subject LIKE %s OR message LIKE %s)';
        $params[] = $like; $params[] = $like; $params[] = $like;
    }
    if ( ! empty( $args['date_range'] ) ) {
        switch ( $args['date_range'] ) {
            case '7days':  $where[] = 'created_at >= %s'; $params[] = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ); break;
            case '30days': $where[] = 'created_at >= %s'; $params[] = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) ); break;
            case '90days': $where[] = 'created_at >= %s'; $params[] = date( 'Y-m-d H:i:s', strtotime( '-90 days' ) ); break;
        }
    }

    $where_sql = implode( ' AND ', $where );
    $offset    = ( max( 1, absint( $args['page'] ) ) - 1 ) * absint( $args['per_page'] );
    $per_page  = absint( $args['per_page'] );

    $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
    $total     = empty( $params ) ? (int) $wpdb->get_var( $count_sql )
                                  : (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

    $data_sql = "SELECT id, to_email, subject, status, created_at FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
    $dp       = array_merge( $params, array( $per_page, $offset ) );
    $rows     = $wpdb->get_results( $wpdb->prepare( $data_sql, $dp ) );

    return array( 'total' => $total, 'rows' => $rows ?: array() );
}

function wph_el_get_log( $id ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wph_email_logs WHERE id = %d", absint( $id )
    ) );
}

function wph_el_delete( $id ) {
    global $wpdb;
    $wpdb->delete( $wpdb->prefix . 'wph_email_logs', array( 'id' => absint( $id ) ), array( '%d' ) );
}

function wph_el_delete_all( $status = '' ) {
    global $wpdb;
    if ( $status ) {
        $wpdb->delete( $wpdb->prefix . 'wph_email_logs', array( 'status' => sanitize_key( $status ) ), array( '%s' ) );
    } else {
        $wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}wph_email_logs" );
    }
}

function wph_el_resend( $id ) {
    $log = wph_el_get_log( $id );
    if ( ! $log ) return new WP_Error( 'not_found', 'Không tìm thấy email' );

    $headers = $log->headers ? explode( "\n", $log->headers ) : array();
    $result  = wp_mail( $log->to_email, $log->subject, $log->message, $headers );
    return $result;
}

function wph_el_get_stats() {
    global $wpdb;
    $table = $wpdb->prefix . 'wph_email_logs';
    return array(
        'total'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ),
        'sent'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status='sent'" ),
        'failed'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status='failed'" ),
        'today'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at)=%s", current_time( 'Y-m-d' ) ) ),
    );
}

// ─── CLEANUP ─────────────────────────────────────────────────────────────────

add_action( 'wph_el_daily_cleanup', 'wph_el_cleanup' );
function wph_el_cleanup() {
    global $wpdb;
    $s = get_option( 'wph_el_settings', array() );
    $r = absint( $s['retention'] ?? 0 );
    if ( $r > 0 ) {
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$wpdb->prefix}wph_email_logs WHERE created_at < %s",
            date( 'Y-m-d H:i:s', strtotime( "-{$r} days" ) )
        ) );
    }
}
if ( ! wp_next_scheduled( 'wph_el_daily_cleanup' ) ) {
    wp_schedule_event( time(), 'daily', 'wph_el_daily_cleanup' );
}

// ─── AJAX: Chart data ─────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_el_chart_data', 'wph_el_ajax_chart_data' );
function wph_el_ajax_chart_data() {
    check_ajax_referer( 'wph_el_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $days = absint( $_POST['days'] ?? 7 );
    $days = in_array( $days, array( 7, 14, 30 ), true ) ? $days : 7;
    global $wpdb;
    $lt   = $wpdb->prefix . 'wph_email_logs';
    $data = array();
    for ( $i = $days - 1; $i >= 0; $i-- ) {
        $d      = date( 'Y-m-d', strtotime( "-{$i} days" ) );
        $data[] = array(
            'date'   => date( 'd/m', strtotime( $d ) ),
            'sent'   => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE DATE(created_at)=%s AND status='sent'", $d ) ),
            'failed' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$lt} WHERE DATE(created_at)=%s AND status='failed'", $d ) ),
        );
    }
    wp_send_json_success( $data );
}

// ─── AJAX: Get email detail ───────────────────────────────────────────────────

add_action( 'wp_ajax_wph_el_get_detail', 'wph_el_ajax_get_detail' );
function wph_el_ajax_get_detail() {
    check_ajax_referer( 'wph_el_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
    $id  = absint( $_POST['id'] ?? 0 );
    $log = wph_el_get_log( $id );
    if ( ! $log ) {
        wp_send_json_error( array( 'message' => 'Không tìm thấy' ) );
    }
    wp_send_json_success( array(
        'id'            => $log->id,
        'to_email'      => $log->to_email,
        'subject'       => $log->subject,
        'message'       => $log->message,
        'headers'       => $log->headers,
        'status'        => $log->status,
        'smtp_response' => $log->smtp_response,
        'created_at'    => $log->created_at,
    ) );
}

// ─── AJAX: CSV Export ─────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_el_export_csv', 'wph_el_ajax_export_csv' );
function wph_el_ajax_export_csv() {
    check_ajax_referer( 'wph_el_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );
    $status = sanitize_key( $_POST['status'] ?? '' );
    $search = sanitize_text_field( $_POST['search'] ?? '' );
    $result = wph_el_get_logs( array( 'status' => $status, 'search' => $search, 'per_page' => 10000, 'page' => 1 ) );
    header( 'Content-Type: text/csv; charset=UTF-8' );
    header( 'Content-Disposition: attachment; filename="email-log-' . date( 'Y-m-d' ) . '.csv"' );
    header( 'Pragma: no-cache' );
    $out = fopen( 'php://output', 'w' );
    fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
    fputcsv( $out, array( 'ID', 'Người nhận', 'Tiêu đề', 'Trạng thái', 'Thời gian gửi' ) );
    foreach ( $result['rows'] as $row ) {
        fputcsv( $out, array(
            $row->id,
            $row->to_email,
            $row->subject,
            $row->status === 'sent' ? 'Thành công' : ( $row->status === 'failed' ? 'Thất bại' : 'Chờ gửi' ),
            $row->created_at,
        ) );
    }
    fclose( $out );
    wp_die();
}
