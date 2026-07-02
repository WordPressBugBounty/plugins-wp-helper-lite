<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── UPDATE STATUS ───────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_update_status', 'wph_fm_ajax_update_status' );
function wph_fm_ajax_update_status() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

    $id     = absint( $_POST['id'] ?? 0 );
    $status = sanitize_key( $_POST['status'] ?? '' );

    if ( ! $id || ! $status ) {
        wp_send_json_error( array( 'message' => 'Thiếu tham số' ) );
    }

    $result = wph_fm_update_status( $id, $status );
    if ( $result !== false ) {
        wp_send_json_success( array( 'message' => 'Đã cập nhật trạng thái', 'badge' => wph_fm_status_badge( $status ) ) );
    } else {
        wp_send_json_error( array( 'message' => 'Không thể cập nhật' ) );
    }
}

// ─── BULK ACTION ─────────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_bulk_action', 'wph_fm_ajax_bulk_action' );
function wph_fm_ajax_bulk_action() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

    $ids    = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
    $action = sanitize_key( $_POST['bulk_action'] ?? '' );

    if ( empty( $ids ) || ! $action ) {
        wp_send_json_error( array( 'message' => 'Thiếu tham số' ) );
    }

    $allowed = array( 'read', 'processing', 'done', 'spam', 'delete' );
    if ( ! in_array( $action, $allowed, true ) ) {
        wp_send_json_error( array( 'message' => 'Hành động không hợp lệ' ) );
    }

    $count = wph_fm_bulk_action( $ids, $action );
    wp_send_json_success( array( 'message' => "Đã xử lý {$count} mục" ) );
}

// ─── DELETE SINGLE ───────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_delete', 'wph_fm_ajax_delete' );
function wph_fm_ajax_delete() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

    $id = absint( $_POST['id'] ?? 0 );
    if ( ! $id ) wp_send_json_error( array( 'message' => 'Thiếu ID' ) );

    wph_fm_delete( $id );
    wp_send_json_success( array( 'message' => 'Đã xóa' ) );
}

// ─── EXPORT ──────────────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_export', 'wph_fm_ajax_export' );
function wph_fm_ajax_export() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized', 403 );

    $format     = sanitize_key( $_GET['format'] ?? 'csv' );
    $status     = sanitize_key( $_GET['status'] ?? '' );
    $form_id    = sanitize_text_field( $_GET['form_id'] ?? '' );
    $date_range = sanitize_key( $_GET['date_range'] ?? '' );
    $date_from  = sanitize_text_field( $_GET['date_from'] ?? '' );
    $date_to    = sanitize_text_field( $_GET['date_to'] ?? '' );

    $data = wph_fm_get_submissions( array(
        'status'     => $status,
        'form_id'    => $form_id,
        'date_range' => $date_range,
        'date_from'  => $date_from,
        'date_to'    => $date_to,
        'per_page'   => 9999,
        'page'       => 1,
    ) );

    $rows     = $data['rows'];
    $filename = 'form-submissions-' . date( 'Ymd-His' );

    if ( $format === 'xlsx' ) {
        wph_fm_export_xlsx( $rows, $filename );
    } else {
        header( 'Content-Type: text/csv; charset=UTF-8' );
        header( "Content-Disposition: attachment; filename={$filename}.csv" );
        header( 'Cache-Control: no-cache, no-store, must-revalidate' );
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        echo wph_fm_export_csv( $rows );
        exit;
    }
}

function wph_fm_export_xlsx( $rows, $filename ) {
    // Simple XML-based XLSX (SpreadsheetML) — không cần thư viện ngoài
    $xml_rows = '';
    $headers  = array( 'ID', 'Form', 'Plugin', 'Họ tên', 'Email', 'SĐT', 'Trạng thái', 'IP', 'Ngày gửi', 'Nội dung' );

    $xml_rows .= '<Row>';
    foreach ( $headers as $h ) {
        $xml_rows .= '<Cell><Data ss:Type="String">' . esc_xml( $h ) . '</Data></Cell>';
    }
    $xml_rows .= '</Row>';

    foreach ( $rows as $r ) {
        $fields = json_decode( $r->submission_data, true );
        $detail = '';
        if ( is_array( $fields ) ) {
            $parts = array();
            foreach ( $fields as $k => $v ) {
                $parts[] = $k . ': ' . ( is_array( $v ) ? implode( ', ', $v ) : $v );
            }
            $detail = implode( ' | ', $parts );
        }
        $cells = array( $r->id, $r->form_title, $r->form_plugin, $r->customer_name, $r->customer_email,
                        $r->customer_phone, wph_fm_status_label( $r->status ), $r->ip_address, $r->created_at, $detail );
        $xml_rows .= '<Row>';
        foreach ( $cells as $c ) {
            $xml_rows .= '<Cell><Data ss:Type="String">' . esc_xml( (string) $c ) . '</Data></Cell>';
        }
        $xml_rows .= '</Row>';
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<?mso-application progid="Excel.Sheet"?>'
        . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
        . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
        . '<Worksheet ss:Name="Form Submissions"><Table>' . $xml_rows . '</Table></Worksheet></Workbook>';

    header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
    header( "Content-Disposition: attachment; filename={$filename}.xls" );
    header( 'Cache-Control: no-cache, no-store, must-revalidate' );
    echo $xml;
    exit;
}

if ( ! function_exists( 'esc_xml' ) ) {
    function esc_xml( $str ) {
        return str_replace( array( '&', '<', '>', '"', "'" ), array( '&amp;', '&lt;', '&gt;', '&quot;', '&apos;' ), $str );
    }
}

// ─── SAVE SETTINGS ───────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_save_settings', 'wph_fm_ajax_save_settings' );
function wph_fm_ajax_save_settings() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

    $plugins_raw = $_POST['plugins'] ?? array();
    $plugins     = array();
    $allowed_plugins = array( 'cf7', 'wpforms', 'gf', 'nf', 'ff', 'frm', 'wsf' );
    foreach ( $allowed_plugins as $p ) {
        $plugins[ $p ] = ! empty( $plugins_raw[ $p ] ) ? '1' : '0';
    }

    $settings = array(
        'active'    => sanitize_text_field( $_POST['active'] ?? '1' ),
        'retention' => absint( $_POST['retention'] ?? 0 ),
        'max_logs'  => absint( $_POST['max_logs']  ?? 0 ),
        'plugins'   => $plugins,
    );

    update_option( 'wph_fm_settings', $settings );
    wp_send_json_success( array( 'message' => 'Đã lưu cài đặt' ) );
}

// ─── MARK READ khi mở chi tiết ───────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_mark_read', 'wph_fm_ajax_mark_read' );
function wph_fm_ajax_mark_read() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

    $id = absint( $_POST['id'] ?? 0 );
    if ( $id ) {
        $row = wph_fm_get_submission( $id );
        if ( $row && $row->status === 'new' ) {
            wph_fm_update_status( $id, 'read' );
        }
    }
    wp_send_json_success();
}

// ─── SEND REPLY ──────────────────────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_send_reply', 'wph_fm_ajax_send_reply' );
function wph_fm_ajax_send_reply() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

    $submission_id = absint( $_POST['submission_id'] ?? 0 );
    $content       = trim( sanitize_textarea_field( $_POST['content'] ?? '' ) );

    if ( ! $submission_id || ! $content ) {
        wp_send_json_error( array( 'message' => 'Thiếu nội dung' ) );
    }

    $sub = wph_fm_get_submission( $submission_id );
    if ( ! $sub ) wp_send_json_error( array( 'message' => 'Không tìm thấy liên hệ' ) );

    $token      = wp_generate_password( 32, false );
    $reply_url  = wph_fm_get_reply_url( $token );
    $site_name  = get_bloginfo( 'name' );
    $admin_name = wp_get_current_user()->display_name ?: get_option( 'blogname' );
    $domain     = parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';
    $thread_id  = wph_fm_thread_message_id( $submission_id );

    // Previous conversations for quoted history
    $prev_conversations = wph_fm_get_conversations( $submission_id );
    $history_html       = wph_fm_email_thread_html( $prev_conversations );

    $site_url   = home_url();
    $site_host  = parse_url( $site_url, PHP_URL_HOST ) ?: $site_url;
    $email_body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif">'
        . '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 16px"><tr><td align="center">'
        . '<table width="100%" style="max-width:580px;border-radius:12px;overflow:hidden">'

        /* ── HEADER ── */
        . '<tr><td style="background:linear-gradient(135deg,#7c3aed 0%,#5b21b6 100%);padding:28px 32px;text-align:center">'
        . '<div style="font-size:22px;font-weight:700;color:#fff;letter-spacing:-0.3px">' . esc_html( $site_name ) . '</div>'
        . '<div style="font-size:13px;color:rgba(255,255,255,.75);margin-top:4px">Phản hồi liên hệ #' . $submission_id . '</div>'
        . '</td></tr>'

        /* ── BODY ── */
        . '<tr><td style="background:#fff;padding:28px 32px">'
        . '<p style="margin:0 0 18px;color:#374151;font-size:15px">Kính gửi <strong>' . esc_html( $sub->customer_name ?: $sub->customer_email ) . '</strong>,</p>'

        /* reply content box */
        . '<div style="background:#faf5ff;border:1px solid #e9d5ff;border-left:4px solid #7c3aed;border-radius:0 8px 8px 0;padding:16px 18px;margin:0 0 20px">'
        . '<div style="font-size:12px;color:#7c3aed;font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">Phản h&#7891;i t&#7915; ' . esc_html( $admin_name ) . '</div>'
        . '<div style="font-size:14px;color:#374151;line-height:1.8">' . nl2br( esc_html( $content ) ) . '</div>'
        . '</div>'

        /* CTA button */
        . '<table cellpadding="0" cellspacing="0" style="margin:0 0 8px"><tr><td>'
        . '<a href="' . esc_url( $reply_url ) . '" style="display:inline-block;background:#7c3aed;color:#fff;text-decoration:none;padding:12px 26px;border-radius:8px;font-size:14px;font-weight:700">&#128172; Nh&#7845;n &#273;&#226;y &#273;&#7875; ph&#7843;n h&#7891;i</a>'
        . '</td></tr></table>'
        . '<p style="font-size:12px;color:#94a3b8;margin:0 0 4px">Ho&#7863;c tr&#7843; l&#7901;i tr&#7921;c ti&#7871;p email n&#224;y.</p>'

        . $history_html
        . '</td></tr>'

        /* ── FOOTER ── */
        . '<tr><td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:16px 32px;text-align:center">'
        . '<div style="font-size:12px;color:#64748b">Email n&#224;y &#273;&#432;&#7907;c g&#7917;i t&#7915; <strong>' . esc_html( $site_name ) . '</strong> &middot; <a href="' . esc_url( $site_url ) . '" style="color:#7c3aed;text-decoration:none">' . esc_html( $site_host ) . '</a></div>'
        . '<div style="font-size:11px;color:#94a3b8;margin-top:4px">Li&#234;n h&#7879; #' . $submission_id . ' &middot; &#272;&#226;y l&#224; email t&#7921; &#273;&#7897;ng ph&#7843;n h&#7891;i li&#234;n h&#7879; c&#7911;a kh&#225;ch h&#224;ng.</div>'
        . '</td></tr>'

        . '</table>'
        . '</td></tr></table>'
        . '</body></html>';

    // From and Message-ID are handled via phpmailer_init hooks below — do NOT put them
    // in $headers here. Passing Message-ID via headers creates a duplicate because
    // PHPMailer also auto-generates one, causing some SMTP servers to reject the DATA
    // command. From is already overridden by whp_smtp_send_mail() anyway.
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'In-Reply-To: ' . $thread_id,
        'References: ' . $thread_id,
    );

    // Set custom Message-ID through the PHPMailer property to avoid duplicates
    $custom_msg_id = '<wph-conv-' . time() . '-' . $submission_id . '@' . $domain . '>';
    $set_msg_id = function( $phpmailer ) use ( $custom_msg_id ) {
        $phpmailer->MessageID = $custom_msg_id;
    };
    add_action( 'phpmailer_init', $set_msg_id, 1 );

    $mail_error = '';
    $mail_error_capture = function( $wp_error ) use ( &$mail_error ) {
        if ( $wp_error instanceof WP_Error ) {
            $mail_error = $wp_error->get_error_message();
        }
    };
    add_action( 'wp_mail_failed', $mail_error_capture, 99 );

    $sent = wp_mail(
        $sub->customer_email,
        'Re: Liên hệ #' . $submission_id . ' – ' . $site_name,
        $email_body,
        $headers
    );

    remove_action( 'phpmailer_init', $set_msg_id, 1 );
    remove_action( 'wp_mail_failed', $mail_error_capture, 99 );

    $conv_id = wph_fm_save_conversation( array(
        'submission_id' => $submission_id,
        'direction'     => 'outbound',
        'author_label'  => $admin_name,
        'content'       => nl2br( esc_html( $content ) ),
        'reply_token'   => $token,
    ) );

    if ( $sub->status === 'new' ) {
        wph_fm_update_status( $submission_id, 'processing' );
    }

    $log_url = admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=email-log' );
    wp_send_json_success( array(
        'message'    => $sent ? 'Đã gửi và lưu phản hồi' : 'Đã lưu nhưng gửi email thất bại' . ( $mail_error ? ': ' . $mail_error : '' ),
        'sent'       => $sent,
        'mail_error' => $mail_error,
        'log_url'    => $log_url,
        'conv'     => array(
            'id'           => $conv_id,
            'direction'    => 'outbound',
            'author_label' => $admin_name,
            'content'      => nl2br( esc_html( $content ) ),
            'created_at'   => current_time( 'mysql' ),
        ),
    ) );
}

// ─── LOAD MORE CONVERSATIONS ─────────────────────────────────────────────────

add_action( 'wp_ajax_wph_fm_load_more_conv', 'wph_fm_ajax_load_more_conv' );
function wph_fm_ajax_load_more_conv() {
    check_ajax_referer( 'wph_fm_nonce', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( array( 'message' => 'Unauthorized' ) );

    $submission_id = absint( $_POST['submission_id'] ?? 0 );
    $offset        = absint( $_POST['offset'] ?? 0 );
    $limit         = 6;

    if ( ! $submission_id ) wp_send_json_error( array( 'message' => 'Thiếu tham số' ) );

    $rows  = wph_fm_get_conversations_paged( $submission_id, $limit, $offset );
    $total = wph_fm_count_conversations( $submission_id );
    $has_more = ( $offset + $limit ) < $total;

    $items = array();
    foreach ( $rows as $c ) {
        $items[] = array(
            'id'           => $c->id,
            'direction'    => $c->direction,
            'author_label' => $c->author_label,
            'content'      => $c->content,
            'created_at'   => date_i18n( 'd/m/Y H:i', strtotime( $c->created_at ) ),
        );
    }

    wp_send_json_success( array(
        'items'    => $items,
        'has_more' => $has_more,
        'next_offset' => $offset + $limit,
    ) );
}
