<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// ─── DB TABLE ────────────────────────────────────────────────────────────────

function wph_fm_create_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'wph_form_submissions';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        form_plugin varchar(50) NOT NULL DEFAULT '',
        form_id varchar(100) NOT NULL DEFAULT '',
        form_title varchar(255) NOT NULL DEFAULT '',
        customer_name varchar(255) NOT NULL DEFAULT '',
        customer_email varchar(255) NOT NULL DEFAULT '',
        customer_phone varchar(100) NOT NULL DEFAULT '',
        status varchar(50) NOT NULL DEFAULT 'new',
        ip_address varchar(100) NOT NULL DEFAULT '',
        user_agent text NOT NULL,
        submission_url varchar(1000) NOT NULL DEFAULT '',
        referrer varchar(1000) NOT NULL DEFAULT '',
        submission_data longtext NOT NULL,
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY form_id (form_id(20)),
        KEY created_at (created_at)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'init', 'wph_fm_create_table' );

function wph_fm_create_conv_table() {
    global $wpdb;
    $table   = $wpdb->prefix . 'wph_fm_conversations';
    $charset = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE {$table} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        submission_id bigint(20) NOT NULL,
        direction varchar(20) NOT NULL DEFAULT 'outbound',
        author_label varchar(255) NOT NULL DEFAULT '',
        content longtext NOT NULL,
        reply_token varchar(64) NOT NULL DEFAULT '',
        created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY submission_id (submission_id),
        KEY reply_token (reply_token(20))
    ) {$charset};";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
add_action( 'init', 'wph_fm_create_conv_table' );

// ─── CRUD ────────────────────────────────────────────────────────────────────

function wph_fm_save_submission( $data ) {
    global $wpdb;

    $settings = get_option( 'wph_fm_settings', array() );
    if ( ! empty( $settings['active'] ) && $settings['active'] === '0' ) return false;

    $row = array(
        'form_plugin'     => sanitize_text_field( $data['form_plugin'] ?? '' ),
        'form_id'         => sanitize_text_field( $data['form_id'] ?? '' ),
        'form_title'      => sanitize_text_field( $data['form_title'] ?? '' ),
        'customer_name'   => sanitize_text_field( $data['customer_name'] ?? '' ),
        'customer_email'  => sanitize_email( $data['customer_email'] ?? '' ),
        'customer_phone'  => sanitize_text_field( $data['customer_phone'] ?? '' ),
        'status'          => 'new',
        'ip_address'      => wph_fm_get_ip(),
        'user_agent'      => substr( $_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500 ),
        'submission_url'  => substr( sanitize_url( $data['submission_url'] ?? '' ), 0, 1000 ),
        'referrer'        => substr( sanitize_url( $data['referrer'] ?? '' ), 0, 1000 ),
        'submission_data' => wp_json_encode( $data['fields'] ?? array(), JSON_UNESCAPED_UNICODE ),
        'created_at'      => current_time( 'mysql' ),
        'updated_at'      => current_time( 'mysql' ),
    );

    $wpdb->insert( $wpdb->prefix . 'wph_form_submissions', $row );
    delete_transient( 'wph_fm_new_count' );
    return $wpdb->insert_id;
}

function wph_fm_get_submissions( $args = array() ) {
    global $wpdb;
    $table = $wpdb->prefix . 'wph_form_submissions';

    $defaults = array(
        'status'     => '',
        'form_id'    => '',
        'form_plugin'=> '',
        'date_range' => '',
        'date_from'  => '',
        'date_to'    => '',
        'search'     => '',
        'per_page'   => 20,
        'page'       => 1,
        'orderby'    => 'created_at',
        'order'      => 'DESC',
    );
    $args = wp_parse_args( $args, $defaults );

    $where  = array( '1=1' );
    $params = array();

    if ( ! empty( $args['status'] ) ) {
        $where[]  = 'status = %s';
        $params[] = $args['status'];
    }
    if ( ! empty( $args['form_id'] ) ) {
        $where[]  = 'form_id = %s';
        $params[] = $args['form_id'];
    }
    if ( ! empty( $args['form_plugin'] ) ) {
        $where[]  = 'form_plugin = %s';
        $params[] = $args['form_plugin'];
    }
    if ( ! empty( $args['search'] ) ) {
        $like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
        $where[]  = '(customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s)';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Date range shortcuts
    if ( ! empty( $args['date_range'] ) ) {
        switch ( $args['date_range'] ) {
            case 'today':
                $where[]  = 'DATE(created_at) = %s';
                $params[] = current_time( 'Y-m-d' );
                break;
            case '7days':
                $where[]  = 'created_at >= %s';
                $params[] = date( 'Y-m-d H:i:s', strtotime( '-7 days' ) );
                break;
            case '30days':
                $where[]  = 'created_at >= %s';
                $params[] = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
                break;
        }
    }
    if ( ! empty( $args['date_from'] ) ) {
        $where[]  = 'created_at >= %s';
        $params[] = sanitize_text_field( $args['date_from'] ) . ' 00:00:00';
    }
    if ( ! empty( $args['date_to'] ) ) {
        $where[]  = 'created_at <= %s';
        $params[] = sanitize_text_field( $args['date_to'] ) . ' 23:59:59';
    }

    $where_sql = implode( ' AND ', $where );
    $order_col = in_array( $args['orderby'], array( 'id', 'created_at', 'status', 'customer_name', 'form_title' ) )
        ? $args['orderby'] : 'created_at';
    $order_dir = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

    $offset   = ( absint( $args['page'] ) - 1 ) * absint( $args['per_page'] );
    $per_page = absint( $args['per_page'] );

    // Total count
    $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
    $total     = empty( $params ) ? (int) $wpdb->get_var( $count_sql )
                                  : (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

    // Rows
    $data_sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$order_col} {$order_dir} LIMIT %d OFFSET %d";
    $data_params = array_merge( $params, array( $per_page, $offset ) );
    $rows     = $wpdb->get_results( $wpdb->prepare( $data_sql, $data_params ) );

    return array( 'total' => $total, 'rows' => $rows ?: array() );
}

function wph_fm_get_submission( $id ) {
    global $wpdb;
    return $wpdb->get_row( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wph_form_submissions WHERE id = %d",
        absint( $id )
    ) );
}

function wph_fm_update_status( $id, $status ) {
    global $wpdb;
    $allowed = array( 'new', 'read', 'processing', 'done', 'spam', 'replied' );
    if ( ! in_array( $status, $allowed, true ) ) return false;
    $result = $wpdb->update(
        $wpdb->prefix . 'wph_form_submissions',
        array( 'status' => $status, 'updated_at' => current_time( 'mysql' ) ),
        array( 'id' => absint( $id ) ),
        array( '%s', '%s' ),
        array( '%d' )
    );
    delete_transient( 'wph_fm_new_count' );
    return $result;
}

function wph_fm_delete( $id ) {
    global $wpdb;
    return $wpdb->delete(
        $wpdb->prefix . 'wph_form_submissions',
        array( 'id' => absint( $id ) ),
        array( '%d' )
    );
}

// Return a stable root Message-ID for a submission thread
function wph_fm_thread_message_id( $submission_id ) {
    $domain = parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost';
    return '<wph-sub-' . (int) $submission_id . '@' . $domain . '>';
}

// Build the threaded email HTML for quoted history
function wph_fm_email_thread_html( $conversations ) {
    if ( empty( $conversations ) ) return '';
    $html  = '<div style="margin-top:24px;border-top:1px solid #e2e8f0;padding-top:18px">';
    $html .= '<div style="font-size:11px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin:0 0 14px">&#128172; L&#7883;ch s&#7917; h&#7897;i tho&#7841;i</div>';
    foreach ( $conversations as $c ) {
        $is_out      = ( $c->direction === 'outbound' );
        $bg          = $is_out ? '#faf5ff' : '#f0f9ff';
        $border      = $is_out ? '#7c3aed' : '#0284c7';
        $label_color = $is_out ? '#7c3aed' : '#0369a1';
        $date        = date_i18n( 'd/m/Y H:i', strtotime( $c->created_at ) );
        $html .= '<div style="margin-bottom:10px;padding:12px 14px;background:' . $bg . ';border-left:3px solid ' . $border . ';border-radius:0 6px 6px 0">';
        $html .= '<div style="font-size:11px;margin-bottom:6px"><strong style="color:' . $label_color . '">' . esc_html( $c->author_label ) . '</strong> <span style="color:#94a3b8">&mdash; ' . $date . '</span></div>';
        $html .= '<div style="font-size:13px;color:#374151;line-height:1.75">' . wp_kses_post( $c->content ) . '</div>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

function wph_fm_get_conversations( $submission_id ) {
    global $wpdb;
    return $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wph_fm_conversations WHERE submission_id=%d ORDER BY created_at ASC",
        $submission_id
    ) );
}

function wph_fm_count_conversations( $submission_id ) {
    global $wpdb;
    return (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}wph_fm_conversations WHERE submission_id=%d",
        $submission_id
    ) );
}

function wph_fm_get_conversations_paged( $submission_id, $limit = 6, $offset = 0 ) {
    global $wpdb;
    // Fetch newest-first then reverse so display order is oldest→newest
    $rows = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wph_fm_conversations WHERE submission_id=%d ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $submission_id, $limit, $offset
    ) );
    return array_reverse( $rows );
}

function wph_fm_save_conversation( $data ) {
    global $wpdb;
    $wpdb->insert( $wpdb->prefix . 'wph_fm_conversations', array(
        'submission_id' => absint( $data['submission_id'] ),
        'direction'     => sanitize_key( $data['direction'] ),
        'author_label'  => sanitize_text_field( $data['author_label'] ?? '' ),
        'content'       => wp_kses_post( $data['content'] ),
        'reply_token'   => sanitize_text_field( $data['reply_token'] ?? '' ),
        'created_at'    => current_time( 'mysql' ),
    ) );
    return $wpdb->insert_id;
}

function wph_fm_get_reply_url( $token ) {
    return add_query_arg( 'wph_fm_reply', $token, home_url( '/' ) );
}

function wph_fm_handle_customer_reply() {
    // ── Success page (GET after PRG redirect) ─────────────────────────────────
    if ( isset( $_GET['wph_fm_done'] ) ) {
        $site_name = get_bloginfo( 'name' );
        ?><!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Đã gửi – <?php echo esc_html( $site_name ); ?></title>
        <style>
        *{box-sizing:border-box;}body{font-family:system-ui,sans-serif;background:#f8fafc;margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;}
        .box{background:#fff;border-radius:20px;padding:48px 40px;text-align:center;max-width:420px;width:90%;box-shadow:0 8px 40px rgba(0,0,0,.1);}
        .icon{width:56px;height:56px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;}
        h2{color:#0f172a;margin:0 0 8px;font-size:20px;}
        p{color:#64748b;margin:0 0 24px;font-size:14px;line-height:1.7;}
        .countdown{font-size:13px;color:#94a3b8;}
        .bar-wrap{height:4px;background:#e2e8f0;border-radius:2px;margin-top:16px;overflow:hidden;}
        .bar{height:4px;background:linear-gradient(90deg,#22c55e,#16a34a);border-radius:2px;width:100%;transition:width 1s linear;}
        </style></head>
        <body>
        <div class="box">
            <div class="icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h2>Đã nhận phản hồi!</h2>
            <p>Cảm ơn bạn! Chúng tôi sẽ liên hệ lại sớm nhất có thể.</p>
            <div class="countdown">Trang này sẽ tự đóng sau <span id="cd">10</span> giây</div>
            <div class="bar-wrap"><div class="bar" id="bar"></div></div>
        </div>
        <script>
        var t = 10;
        var cd = document.getElementById('cd');
        var bar = document.getElementById('bar');
        var iv = setInterval(function(){
            t--;
            if(cd) cd.textContent = t;
            if(bar) bar.style.width = (t/10*100) + '%';
            if(t <= 0){
                clearInterval(iv);
                // Try to close tab; fallback to blank
                try{ window.close(); }catch(e){}
                setTimeout(function(){ window.location.replace('about:blank'); }, 300);
            }
        }, 1000);
        </script>
        </body></html><?php
        exit;
    }

    // ── Reply form (GET) + submission (POST) ─────────────────────────────────
    $token = isset( $_GET['wph_fm_reply'] ) ? sanitize_text_field( $_GET['wph_fm_reply'] ) : '';
    if ( ! $token ) return;

    global $wpdb;
    $conv = $wpdb->get_row( $wpdb->prepare(
        "SELECT c.*, s.customer_name, s.customer_email, s.id as sub_id
         FROM {$wpdb->prefix}wph_fm_conversations c
         JOIN {$wpdb->prefix}wph_form_submissions s ON c.submission_id = s.id
         WHERE c.reply_token = %s AND c.direction = 'outbound'
         ORDER BY c.id DESC LIMIT 1",
        $token
    ) );

    if ( ! $conv ) {
        wp_die( 'Liên kết phản hồi không hợp lệ hoặc đã được sử dụng.', 'Hết hạn', array( 'response' => 410 ) );
    }

    // Load full conversation history for this submission
    $all_conversations = wph_fm_get_conversations( $conv->sub_id );

    if ( isset( $_POST['wph_fm_reply_submit'] ) ) {
        check_admin_referer( 'wph_fm_customer_reply_' . $token );
        $reply_content = sanitize_textarea_field( $_POST['wph_fm_reply_content'] ?? '' );
        if ( $reply_content ) {
            wph_fm_save_conversation( array(
                'submission_id' => $conv->sub_id,
                'direction'     => 'inbound',
                'author_label'  => $conv->customer_name ?: $conv->customer_email,
                'content'       => nl2br( esc_html( $reply_content ) ),
                'reply_token'   => '',
            ) );
            // Invalidate token — prevents duplicate submission on refresh
            $wpdb->update(
                $wpdb->prefix . 'wph_fm_conversations',
                array( 'reply_token' => '' ),
                array( 'reply_token' => $token ),
                array( '%s' ), array( '%s' )
            );
            wph_fm_update_status( $conv->sub_id, 'replied' );
            $admin_email   = get_option( 'admin_email' );
            $site_name_m   = get_bloginfo( 'name' );
            $customer_name = $conv->customer_name ?: $conv->customer_email;
            $thread_id     = wph_fm_thread_message_id( $conv->sub_id );
            // Dùng SMTP email (tài khoản xác thực) làm From để tránh Gmail 554 "Data not accepted"
            $smtp_from_email = whp_get_setting( 'whp_smtp_email' ) ?: $admin_email;
            $site_from       = get_bloginfo( 'name' ) . ' <' . $smtp_from_email . '>';
            $admin_headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $site_from,
                'Reply-To: ' . $customer_name . ' <' . ( $conv->customer_email ?: $admin_email ) . '>',
                'Message-ID: <wph-inbound-' . time() . '@' . ( parse_url( home_url(), PHP_URL_HOST ) ?: 'localhost' ) . '>',
                'In-Reply-To: ' . $thread_id,
                'References: ' . $thread_id,
            );
            // Load fresh history including new reply for quoted thread
            $history_html = wph_fm_email_thread_html( wph_fm_get_conversations( $conv->sub_id ) );
            $admin_body   = '<div style="font-family:sans-serif;font-size:14px;line-height:1.7;color:#374151;max-width:600px;margin:auto;padding:24px;">'
                . '<p><strong>' . esc_html( $customer_name ) . '</strong> vừa phản hồi liên hệ <strong>#' . (int) $conv->sub_id . '</strong>:</p>'
                . '<div style="background:#eff6ff;border-left:3px solid #2563eb;border-radius:0 8px 8px 0;padding:12px 16px;margin:16px 0;">'
                . '<div style="font-size:13px;color:#374151;line-height:1.7;">' . nl2br( esc_html( $reply_content ) ) . '</div>'
                . '</div>'
                . $history_html
                . '</div>';
            wp_mail( $admin_email,
                'Re: Liên hệ #' . $conv->sub_id . ' – ' . $site_name_m,
                $admin_body,
                $admin_headers
            );
            // PRG: redirect to success page — refresh won't resubmit
            wp_redirect( add_query_arg( 'wph_fm_done', '1', home_url( '/' ) ) );
            exit;
        }
    }

    $site_name = get_bloginfo( 'name' );
    ?><!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Phản hồi – <?php echo esc_html( $site_name ); ?></title>
    <style>
    *{box-sizing:border-box;}body{font-family:system-ui,sans-serif;background:#f8fafc;margin:0;padding:20px;}
    .wrap{max-width:600px;margin:40px auto;}
    .card{background:#fff;border-radius:16px;padding:28px;box-shadow:0 4px 24px rgba(0,0,0,.08);}
    h1{font-size:18px;margin:0 0 4px;color:#0f172a;}
    .sub{color:#64748b;font-size:13px;margin:0 0 20px;}
    .thread{margin-bottom:20px;}
    .msg{margin-bottom:10px;padding:10px 14px;border-radius:0 8px 8px 0;font-size:13px;line-height:1.7;}
    .msg-out{background:#faf5ff;border-left:3px solid #7c3aed;}
    .msg-in{background:#eff6ff;border-left:3px solid #2563eb;}
    .msg-meta{font-size:11px;color:#94a3b8;margin-bottom:4px;}
    .msg-meta strong{color:#64748b;}
    textarea{width:100%;min-height:120px;border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 12px;font-size:14px;font-family:inherit;resize:vertical;outline:none;transition:border .15s;}
    textarea:focus{border-color:#7c3aed;}
    button{width:100%;padding:12px;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;margin-top:12px;}
    .compose-label{font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;display:block;}
    </style></head>
    <body>
    <div class="wrap">
      <div class="card">
        <h1><?php echo esc_html( $site_name ); ?></h1>
        <p class="sub">Liên hệ #<?php echo (int)$conv->sub_id; ?> — <?php echo esc_html( $conv->customer_name ?: $conv->customer_email ); ?></p>
        <?php if ( ! empty( $all_conversations ) ) : ?>
        <div class="thread">
          <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Lịch sử hội thoại</div>
          <?php foreach ( $all_conversations as $c ) : ?>
          <div class="msg msg-<?php echo $c->direction === 'outbound' ? 'out' : 'in'; ?>">
            <div class="msg-meta">
              <strong><?php echo esc_html( $c->author_label ); ?></strong>
              — <?php echo date_i18n( 'd/m/Y H:i', strtotime( $c->created_at ) ); ?>
            </div>
            <?php echo wp_kses_post( $c->content ); ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <form method="post">
          <?php wp_nonce_field( 'wph_fm_customer_reply_' . $token ); ?>
          <label class="compose-label">Phản hồi của bạn</label>
          <textarea name="wph_fm_reply_content" placeholder="Nhập nội dung phản hồi…" required></textarea>
          <button type="submit" name="wph_fm_reply_submit" value="1">Gửi phản hồi</button>
        </form>
      </div>
    </div>
    </body></html><?php
    exit;
}
add_action( 'init', 'wph_fm_handle_customer_reply' );

function wph_fm_bulk_action( $ids, $action ) {
    if ( empty( $ids ) || ! is_array( $ids ) ) return 0;
    $count = 0;
    foreach ( $ids as $id ) {
        if ( $action === 'delete' ) {
            if ( wph_fm_delete( $id ) ) $count++;
        } else {
            if ( wph_fm_update_status( $id, $action ) !== false ) $count++;
        }
    }
    return $count;
}

// ─── STATS ───────────────────────────────────────────────────────────────────

function wph_fm_get_stats() {
    global $wpdb;
    $table = $wpdb->prefix . 'wph_form_submissions';

    $total      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
    $today      = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at) = %s", current_time( 'Y-m-d' )
    ) );
    $processing = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'processing'" );
    $done       = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'done'" );
    $new_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'new'" );

    return compact( 'total', 'today', 'processing', 'done', 'new_count' );
}

function wph_fm_get_chart_data( $days = 7 ) {
    global $wpdb;
    $table  = $wpdb->prefix . 'wph_form_submissions';
    $result = array();

    for ( $i = $days - 1; $i >= 0; $i-- ) {
        $date    = date( 'Y-m-d', strtotime( "-{$i} days" ) );
        $label   = date( 'd/m', strtotime( "-{$i} days" ) );
        $count   = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at) = %s", $date
        ) );
        $result[] = array( 'date' => $label, 'count' => $count );
    }
    return $result;
}

function wph_fm_get_forms_list() {
    global $wpdb;
    $table = $wpdb->prefix . 'wph_form_submissions';
    return $wpdb->get_results(
        "SELECT DISTINCT form_id, form_title, form_plugin FROM {$table} ORDER BY form_title ASC"
    );
}

// ─── EXPORT ──────────────────────────────────────────────────────────────────

function wph_fm_export_csv( $rows ) {
    $lines   = array();
    $lines[] = implode( ',', array( 'ID', 'Form', 'Plugin', 'Họ tên', 'Email', 'SĐT', 'Trạng thái', 'IP', 'Ngày gửi', 'Nội dung' ) );
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
        $lines[] = implode( ',', array_map( function( $v ) {
            return '"' . str_replace( '"', '""', $v ) . '"';
        }, array(
            $r->id, $r->form_title, $r->form_plugin,
            $r->customer_name, $r->customer_email, $r->customer_phone,
            wph_fm_status_label( $r->status ),
            $r->ip_address, $r->created_at, $detail,
        ) ) );
    }
    return implode( "\n", $lines );
}

// ─── HELPERS ─────────────────────────────────────────────────────────────────

function wph_fm_get_ip() {
    foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ) as $key ) {
        if ( ! empty( $_SERVER[ $key ] ) ) {
            $ip = trim( explode( ',', $_SERVER[ $key ] )[0] );
            if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) return $ip;
        }
    }
    return '';
}

function wph_fm_status_label( $status ) {
    $map = array(
        'new'        => __( 'Mới', 'whp' ),
        'read'       => __( 'Đã đọc', 'whp' ),
        'processing' => __( 'Đang xử lý', 'whp' ),
        'replied'    => __( 'Khách phản hồi', 'whp' ),
        'done'       => __( 'Hoàn thành', 'whp' ),
        'spam'       => __( 'Spam', 'whp' ),
    );
    return $map[ $status ] ?? $status;
}

function wph_fm_status_badge( $status ) {
    $colors = array(
        'new'        => array( 'bg' => '#eff6ff', 'color' => '#2563eb', 'label' => __( 'Mới', 'whp' ) ),
        'read'       => array( 'bg' => '#f1f5f9', 'color' => '#475569', 'label' => __( 'Đã đọc', 'whp' ) ),
        'processing' => array( 'bg' => '#fffbeb', 'color' => '#d97706', 'label' => __( 'Đang xử lý', 'whp' ) ),
        'replied'    => array( 'bg' => '#fdf4ff', 'color' => '#9333ea', 'label' => __( 'Khách phản hồi', 'whp' ) ),
        'done'       => array( 'bg' => '#f0fdf4', 'color' => '#16a34a', 'label' => __( 'Hoàn thành', 'whp' ) ),
        'spam'       => array( 'bg' => '#fef2f2', 'color' => '#dc2626', 'label' => __( 'Spam', 'whp' ) ),
    );
    $s   = $colors[ $status ] ?? $colors['read'];
    return sprintf(
        '<span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;background:%s;color:%s;">%s</span>',
        esc_attr( $s['bg'] ), esc_attr( $s['color'] ), esc_html( $s['label'] )
    );
}

// ─── ADMIN MENU BADGE ────────────────────────────────────────────────────────

function wph_fm_new_count_cached() {
    $count = get_transient( 'wph_fm_new_count' );
    if ( $count === false ) {
        global $wpdb;
        $count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wph_form_submissions WHERE status = 'new'" );
        set_transient( 'wph_fm_new_count', $count, 120 );
    }
    return (int) $count;
}

add_action( 'admin_menu', function() {
    if ( ! current_user_can( 'manage_options' ) ) return;
    $count = wph_fm_new_count_cached();
    if ( $count < 1 ) return;
    global $submenu;
    if ( empty( $submenu['mb-wphelper'] ) ) return;
    foreach ( $submenu['mb-wphelper'] as $key => $item ) {
        if ( isset( $item[2] ) && $item[2] === 'mb-wphelper-smtp' ) {
            $submenu['mb-wphelper'][ $key ][0] .= ' <span class="update-plugins count-' . $count . '"><span class="plugin-count">' . $count . '</span></span>';
            break;
        }
    }
}, 999 );

// ─── CLEANUP (theo retention setting) ────────────────────────────────────────

function wph_fm_cleanup_old_records() {
    global $wpdb;
    $settings  = get_option( 'wph_fm_settings', array() );
    $table     = $wpdb->prefix . 'wph_form_submissions';
    $retention = absint( $settings['retention'] ?? 0 );
    if ( $retention > 0 ) {
        $wpdb->query( $wpdb->prepare(
            "DELETE FROM {$table} WHERE created_at < %s",
            date( 'Y-m-d H:i:s', strtotime( "-{$retention} days" ) )
        ) );
    }
    $max = absint( $settings['max_logs'] ?? 0 );
    if ( $max > 0 ) {
        $min_id = $wpdb->get_var( "SELECT MIN(id) FROM (SELECT id FROM {$table} ORDER BY id DESC LIMIT {$max}) t" );
        if ( $min_id ) {
            $wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE id < %d", $min_id ) );
        }
    }
}
add_action( 'wph_fm_daily_cleanup', 'wph_fm_cleanup_old_records' );
if ( ! wp_next_scheduled( 'wph_fm_daily_cleanup' ) ) {
    wp_schedule_event( time(), 'daily', 'wph_fm_daily_cleanup' );
}
