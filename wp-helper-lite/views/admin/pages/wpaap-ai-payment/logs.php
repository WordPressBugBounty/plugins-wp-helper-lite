<?php
defined( 'ABSPATH' ) || exit;

// Legacy hero function kept for any direct callers
function wpaap_aipay_logs_hero( $count = 0 ) {}

function wpaap_aipay_logs_layout() {

    // ── GET params ───────────────────────────────────────────────────────────────
    $search    = isset( $_GET['apl_s']    ) ? sanitize_text_field( wp_unslash( $_GET['apl_s']    ) ) : '';
    $f_action  = isset( $_GET['apl_act']  ) ? sanitize_key( $_GET['apl_act']  ) : '';
    $f_source  = isset( $_GET['apl_src']  ) ? sanitize_key( $_GET['apl_src']  ) : '';
    $f_user_t  = isset( $_GET['apl_usr']  ) ? sanitize_key( $_GET['apl_usr']  ) : '';
    $f_from    = isset( $_GET['apl_from'] ) ? sanitize_text_field( wp_unslash( $_GET['apl_from'] ) ) : '';
    $f_to      = isset( $_GET['apl_to']   ) ? sanitize_text_field( wp_unslash( $_GET['apl_to']   ) ) : '';
    $sort_dir  = ( isset( $_GET['apl_sort'] ) && $_GET['apl_sort'] === 'asc' ) ? 'asc' : 'desc';
    $per_page  = 15;
    $cur_page  = max( 1, absint( $_GET['apl_page'] ?? 1 ) );
    $base_url  = admin_url( 'admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=logs' );

    // ── Action type definitions ──────────────────────────────────────────────────
    $actions_def = [
        'verify'    => [ __( 'Xác minh thanh toán', 'whp' ), '#10b981', '#ecfdf5' ],
        'ai'        => [ __( 'Phân tích AI',         'whp' ), '#8b5cf6', '#f5f3ff' ],
        'reject'    => [ __( 'Từ chối thanh toán',   'whp' ), '#ef4444', '#fef2f2' ],
        'risk'      => [ __( 'Giao dịch rủi ro',     'whp' ), '#f97316', '#fff7ed' ],
        'update'    => [ __( 'Cập nhật trạng thái',  'whp' ), '#3b82f6', '#eff6ff' ],
        'notify'    => [ __( 'Gửi thông báo',        'whp' ), '#eab308', '#fefce8' ],
        'create'    => [ __( 'Tạo đơn hàng',         'whp' ), '#0ea5e9', '#f0f9ff' ],
    ];

    // ── Source definitions ───────────────────────────────────────────────────────
    $sources_def = [
        'web'    => [ 'Web',    '#3b82f6' ],
        'ai-ocr' => [ 'AI OCR', '#8b5cf6' ],
        'api'    => [ 'API',    '#10b981' ],
        'email'  => [ 'Email',  '#f97316' ],
        'system' => [ 'System', '#64748b' ],
    ];

    // ── Load all orders with transfer confirmation ───────────────────────────────
    $all_ids = wc_get_orders( [
        'limit'      => -1,
        'return'     => 'ids',
        'orderby'    => 'date',
        'order'      => 'DESC',
        'meta_query' => [
            [ 'key' => '_whp_transfer_confirmed_at', 'compare' => 'EXISTS' ],
        ],
    ] );

    // ── Build log entries ────────────────────────────────────────────────────────
    $all_entries = [];
    foreach ( $all_ids as $oid ) {
        $o = wc_get_order( $oid );
        if ( ! $o ) continue;

        $confirmed_at_raw = $o->get_meta( '_whp_transfer_confirmed_at' );
        $confirmed_at     = $confirmed_at_raw ? (int) strtotime( $confirmed_at_raw ) : 0;
        $ai_result    = $o->get_meta( '_whp_ai_verify_result' );
        $receipt_url  = $o->get_meta( '_whp_transfer_receipt' );
        $bank         = $o->get_meta( '_whp_transfer_bank' );
        $sender       = $o->get_meta( '_whp_transfer_sender' );
        $status       = $o->get_status();
        $f_name       = $o->get_billing_first_name();
        $l_name       = $o->get_billing_last_name();
        $customer     = trim( "$f_name $l_name" ) ?: ( $o->get_billing_email() ?: __( 'Khách hàng', 'whp' ) );

        // Determine action type
        if ( in_array( $status, [ 'cancelled', 'failed' ], true ) ) {
            $action = 'reject';
        } elseif ( $ai_result ) {
            $action = 'ai';
        } elseif ( in_array( $status, [ 'completed', 'processing' ], true ) ) {
            $action = 'verify';
        } else {
            $action = 'update';
        }

        // Determine source
        $source     = $receipt_url ? 'ai-ocr' : 'web';
        $user_type  = ( $source === 'ai-ocr' ) ? 'ai' : 'customer';
        $user_label = ( $source === 'ai-ocr' ) ? 'AI System' : $customer;

        // Content summary
        $amount_raw = wc_price( $o->get_total(), [ 'currency' => $o->get_currency() ] );
        $parts      = [ strip_tags( $amount_raw ) ];
        if ( $bank   ) $parts[] = $bank;
        if ( $sender ) $parts[] = $sender;
        $content = implode( ' · ', array_filter( $parts ) ) ?: '—';

        $date_created = $o->get_date_created();
        $entry_ts     = $confirmed_at ?: ( $date_created ? (int) $date_created->getTimestamp() : time() );
        $order_url    = $o->get_edit_order_url() ?: admin_url( 'post.php?post=' . $oid . '&action=edit' );

        $all_entries[] = [
            'time'       => $entry_ts,
            'action'     => $action,
            'content'    => $content,
            'object'     => '#' . $oid,
            'object_url' => $order_url,
            'user_label' => $user_label,
            'user_type'  => $user_type,
            'source'     => $source,
            'order_id'   => $oid,
            'receipt'    => $receipt_url,
        ];
    }

    // ── Sort ─────────────────────────────────────────────────────────────────────
    usort( $all_entries, function ( $a, $b ) use ( $sort_dir ) {
        return $sort_dir === 'asc' ? $a['time'] - $b['time'] : $b['time'] - $a['time'];
    } );

    // ── Apply filters ─────────────────────────────────────────────────────────────
    $ts_from  = $f_from ? (int) strtotime( str_replace( '/', '-', $f_from ) )                  : 0;
    $ts_to    = $f_to   ? (int) strtotime( str_replace( '/', '-', $f_to   ) . ' 23:59:59' )   : 0;
    $filtered = [];
    foreach ( $all_entries as $e ) {
        if ( $f_action && $e['action'] !== $f_action ) continue;
        if ( $f_source && $e['source'] !== $f_source ) continue;
        if ( $f_user_t === 'ai'       && $e['user_type'] !== 'ai'       ) continue;
        if ( $f_user_t === 'customer' && $e['user_type'] !== 'customer' ) continue;
        if ( $ts_from && $e['time'] < $ts_from ) continue;
        if ( $ts_to   && $e['time'] > $ts_to   ) continue;
        if ( $search ) {
            $hay = $e['object'] . ' ' . $e['user_label'] . ' ' . $e['content'];
            if ( stripos( $hay, $search ) === false ) continue;
        }
        $filtered[] = $e;
    }

    // ── Pagination ────────────────────────────────────────────────────────────────
    $total     = count( $filtered );
    $log_pages = max( 1, (int) ceil( $total / $per_page ) );
    $page_rows = array_slice( $filtered, ( $cur_page - 1 ) * $per_page, $per_page );
    $from_num  = $total ? ( $cur_page - 1 ) * $per_page + 1 : 0;
    $to_num    = $total ? min( $cur_page * $per_page, $total ) : 0;

    // ── Sidebar data ──────────────────────────────────────────────────────────────
    $today_ts    = (int) strtotime( date( 'Y-m-d' ) );
    $yest_ts     = $today_ts - DAY_IN_SECONDS;
    $rt_feed     = array_slice( $all_entries, 0, 5 );
    $today_count = 0;
    $yest_count  = 0;
    $src_counts  = [ 'web' => 0, 'ai-ocr' => 0, 'api' => 0, 'email' => 0, 'system' => 0 ];
    $act_counts  = [];

    foreach ( $all_entries as $e ) {
        if ( $e['time'] >= $today_ts )                                   $today_count++;
        if ( $e['time'] >= $yest_ts && $e['time'] < $today_ts )         $yest_count++;
        if ( array_key_exists( $e['source'], $src_counts ) )            $src_counts[ $e['source'] ]++;
        $act_counts[ $e['action'] ] = ( $act_counts[ $e['action'] ] ?? 0 ) + 1;
    }
    arsort( $act_counts );

    // ── Donut chart SVG data ──────────────────────────────────────────────────────
    $src_total_all = array_sum( $src_counts );
    $donut_r       = 50;
    $circumf       = 2 * M_PI * $donut_r; // ≈ 314.16
    $donut_offset  = 0.0;
    $donut_segs    = [];
    $src_order     = [ 'web', 'ai-ocr', 'api', 'email', 'system' ];
    foreach ( $src_order as $sk ) {
        $cnt  = $src_counts[ $sk ] ?? 0;
        $pct  = $src_total_all > 0 ? $cnt / $src_total_all : 0;
        $len  = $pct * $circumf;
        $gap  = $circumf - $len;
        $donut_segs[] = [
            'key'    => $sk,
            'label'  => $sources_def[ $sk ][0],
            'color'  => $sources_def[ $sk ][1],
            'pct'    => $pct,
            'count'  => $cnt,
            'len'    => $len,
            'gap'    => $gap,
            'offset' => -$donut_offset,
        ];
        $donut_offset += $len;
    }

    // ── Build paginate URL helper ─────────────────────────────────────────────────
    $filter_qs = http_build_query( array_filter( [
        'apl_s'    => $search,
        'apl_act'  => $f_action,
        'apl_src'  => $f_source,
        'apl_usr'  => $f_user_t,
        'apl_from' => $f_from,
        'apl_to'   => $f_to,
        'apl_sort' => $sort_dir !== 'desc' ? $sort_dir : '',
    ] ) );
    $paginate_base = $base_url . ( $filter_qs ? '&' . $filter_qs : '' );
    $has_filters   = ( $search || $f_action || $f_source || $f_user_t || $f_from || $f_to );

    // ── Time-ago helper ───────────────────────────────────────────────────────────
    function aipl_time_ago( $ts ) {
        $diff = time() - $ts;
        if ( $diff < 60 )     return __( 'Vừa xong', 'whp' );
        if ( $diff < 3600 )   return floor( $diff / 60 ) . ' ' . __( 'phút trước', 'whp' );
        if ( $diff < 86400 )  return floor( $diff / 3600 ) . ' ' . __( 'giờ trước', 'whp' );
        if ( $diff < 604800 ) return floor( $diff / 86400 ) . ' ' . __( 'ngày trước', 'whp' );
        return date_i18n( 'd/m/Y', $ts );
    }
    ?>

<style>
/* ── Layout ─────────────────────────────────────────────────────────────────── */
.aipl-wrap {
    display: flex;
    flex-direction: column;
    gap: 14px;
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 0 48px;
    font-family: inherit;
}
.aipl-body {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 20px;
    align-items: start;
}
@media (max-width: 1100px) { .aipl-body { grid-template-columns: 1fr; } }
@media (max-width: 768px)  { .aipl-wrap { padding-bottom: 24px; } }

/* ── Filter bar ─────────────────────────────────────────────────────────────── */
.aipl-filter-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 16px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.aipl-search-wrap {
    flex: 1 1 220px;
    position: relative;
    min-width: 0;
}
.aipl-search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #94a3b8; display: block; flex-shrink: 0; }
.aipl-search-inp {
    width: 100%; height: 36px; box-sizing: border-box;
    padding: 0 12px 0 38px !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important;
    font-size: 12.5px !important;
    font-family: inherit !important;
    background: #fff !important;
    color: #0f172a !important;
    outline: none !important;
    box-shadow: none !important;
    transition: border-color .2s;
}
.aipl-search-inp:focus { border-color: #059669 !important; box-shadow: 0 0 0 3px rgba(5,150,105,.12) !important; }
/* ── Custom dropdown ─────────────────────────────────────────────────────────── */
.aipl-csel { position: relative; flex-shrink: 0; }
.aipl-csel-btn {
    height: 36px; padding: 0 30px 0 26px;
    border-radius: 8px; border: 1px solid #e2e8f0;
    background: #fff; cursor: pointer; white-space: nowrap;
    display: inline-flex; align-items: center; gap: 7px;
    font-size: 12.5px; font-family: inherit; color: #0f172a;
    transition: border-color .2s; outline: none; position: relative;
}
.aipl-csel-btn:hover,
.aipl-csel.open .aipl-csel-btn { border-color: #059669; }
.aipl-csel-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
    transition: background .15s;
}
.aipl-csel-chevron {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    color: #94a3b8; pointer-events: none;
    transition: transform .2s;
}
.aipl-csel.open .aipl-csel-chevron { transform: translateY(-50%) rotate(180deg); }
.aipl-csel-menu {
    display: none; position: absolute; top: calc(100% + 5px); left: 0;
    min-width: 100%; background: #fff;
    border: 1px solid #e2e8f0; border-radius: 10px;
    box-shadow: 0 8px 24px rgba(15,23,42,.12);
    z-index: 99999; padding: 4px 0; min-width: 180px;
}
.aipl-csel.open .aipl-csel-menu { display: block; }
.aipl-csel-opt {
    display: flex; align-items: center; gap: 9px;
    padding: 8px 12px; cursor: pointer;
    font-size: 12.5px; color: #1e293b;
    transition: background .1s; user-select: none;
}
.aipl-csel-opt:hover { background: #f8fafc; }
.aipl-csel-opt.selected { background: #f0fdf4; }
.aipl-csel-opt-dot {
    width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
}
.aipl-csel-opt-label { flex: 1; }
.aipl-csel-opt-check {
    flex-shrink: 0; opacity: 0;
    color: #10b981; transition: opacity .15s;
}
.aipl-csel-opt.selected .aipl-csel-opt-check { opacity: 1; }
.aipl-daterange {
    display: inline-flex; align-items: center; gap: 4px;
    height: 36px; padding: 0 10px;
    border: 1px solid #e2e8f0; border-radius: 8px;
    background: #fff; flex-shrink: 0;
    transition: border-color .2s;
}
.aipl-daterange:focus-within { border-color: #059669; }
.aipl-daterange > svg { color: #94a3b8; flex-shrink: 0; }
.aipl-daterange-sep { font-size: 11px; color: #cbd5e1; flex-shrink: 0; }
.aipl-date-inp {
    border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent !important;
    padding: 0 !important; margin: 0 !important;
    font-size: 12px !important; font-family: inherit !important;
    color: #475569 !important; cursor: pointer;
    width: 90px; height: auto;
}
.aipl-date-inp::-webkit-calendar-picker-indicator { opacity: 0; cursor: pointer; width: 100%; height: 100%; position: absolute; top: 0; left: 0; }
.aipl-date-inp { position: relative; }
.aipl-date-inp::-webkit-inner-spin-button { display: none; }
.aipl-date-inp:focus { border-color: #059669; outline: none; }
.aipl-btn {
    height: 36px; padding: 0 14px; border-radius: 8px;
    border: none; font-size: 12.5px; font-weight: 600; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
    transition: transform .12s, box-shadow .12s;
    flex-shrink: 0;
}
.aipl-btn-primary {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff;
    box-shadow: 0 2px 6px rgba(5,150,105,.3);
}
.aipl-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(5,150,105,.35); }
.aipl-btn-reset {
    background: #fff; border: 1px solid #e2e8f0;
    color: #64748b;
    height: 36px; width: 36px; padding: 0;
    border-radius: 8px; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: background .12s, border-color .12s;
    flex-shrink: 0;
}
.aipl-btn-reset:hover { background: #f8fafc; border-color: #cbd5e1; }
.aipl-btn-refresh {
    background: #fff; border: 1px solid #e2e8f0;
    color: #64748b;
    height: 36px; width: 36px; padding: 0;
    border-radius: 8px; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: background .12s; flex-shrink: 0; text-decoration: none;
}
.aipl-btn-refresh:hover { background: #f0fdf4; border-color: #a7f3d0; color: #059669; }

/* ── Table card ─────────────────────────────────────────────────────────────── */
.aipl-card {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.aipl-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    flex-wrap: wrap;
}
.aipl-card-head h3 {
    margin: 0; font-size: 14.5px; font-weight: 700; color: #0f172a;
    display: flex; align-items: center; gap: 8px;
}
.aipl-count-badge {
    background: #ecfdf5; color: #059669;
    padding: 2px 8px; border-radius: 20px;
    font-size: 11px; font-weight: 700;
}
.aipl-head-meta { font-size: 12px; color: #94a3b8; }
.aipl-table-wrap { overflow-x: auto; }
.aipl-table {
    width: 100%; border-collapse: collapse;
    font-size: 12.5px; min-width: 780px;
}
.aipl-table th {
    background: #f8fafc; padding: 9px 14px;
    text-align: left; font-weight: 700;
    font-size: 10px; text-transform: uppercase;
    letter-spacing: .5px; color: #64748b;
    border-bottom: 1px solid #e2e8f0;
    white-space: nowrap; cursor: pointer; user-select: none;
}
.aipl-table th:first-child { padding-left: 18px; }
.aipl-table th:last-child  { padding-right: 18px; }
.aipl-table td {
    padding: 11px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle; color: #1e293b;
}
.aipl-table td:first-child { padding-left: 18px; }
.aipl-table td:last-child  { padding-right: 18px; }
.aipl-table tr:last-child td { border-bottom: none; }
.aipl-table tr:hover td { background: #f8fffe; }

/* ── Cell styles ────────────────────────────────────────────────────────────── */
.aipl-time { color: #475569; font-size: 11.5px; white-space: nowrap; }
.aipl-time-rel { color: #94a3b8; font-size: 10.5px; display: block; margin-top: 2px; }
.aipl-action-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 20px;
    font-size: 11px; font-weight: 700; white-space: nowrap;
}
.aipl-action-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.aipl-content { color: #475569; font-size: 12px; max-width: 180px; }
.aipl-object-link {
    color: #059669; font-weight: 700; text-decoration: none; font-size: 12.5px;
    display: inline-flex; align-items: center; gap: 4px;
}
.aipl-object-link:hover { color: #047857; text-decoration: underline; }
.aipl-user { color: #1e293b; font-size: 12px; font-weight: 600; }
.aipl-user-sub { color: #94a3b8; font-size: 10.5px; }
.aipl-src-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: 6px;
    font-size: 11px; font-weight: 600;
    background: #f8fafc; color: #475569;
}
.aipl-src-dot { width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
.aipl-detail-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 7px;
    background: #f0fdf4; border: 1px solid #a7f3d0;
    color: #059669; text-decoration: none;
    transition: background .12s, transform .12s;
}
.aipl-detail-btn:hover { background: #dcfce7; transform: translateY(-1px); }

/* ── Sort indicator ────────────────────────────────────────────────────────── */
.aipl-sort-arrow { display: inline-block; margin-left: 4px; opacity: .5; }
.aipl-sort-arrow.active { opacity: 1; color: #059669; }

/* ── Empty ──────────────────────────────────────────────────────────────────── */
.aipl-empty {
    padding: 64px 24px; text-align: center;
}
.aipl-empty-icon {
    width: 56px; height: 56px; border-radius: 16px;
    background: #ecfdf5; border: 1px solid #a7f3d0;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.aipl-empty h3 { margin: 0 0 8px; font-size: 15px; font-weight: 700; color: #0f172a; }
.aipl-empty p  { margin: 0; font-size: 13px; color: #64748b; line-height: 1.7; }

/* ── Pagination ─────────────────────────────────────────────────────────────── */
.aipl-pagination {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px; border-top: 1px solid #f1f5f9;
    font-size: 12px; color: #64748b; flex-wrap: wrap; gap: 8px;
}
.aipl-page-links { display: flex; gap: 4px; flex-wrap: wrap; }
.aipl-page-links a,
.aipl-page-links span {
    padding: 4px 10px; border-radius: 6px;
    border: 1px solid #e2e8f0;
    text-decoration: none; color: #475569;
    font-size: 12px; font-weight: 500;
    transition: background .1s;
}
.aipl-page-links a:hover  { background: #f0fdf4; border-color: #a7f3d0; color: #059669; }
.aipl-page-links span.current {
    background: linear-gradient(135deg, #059669, #10b981);
    color: #fff; border-color: #059669; font-weight: 700;
}
.aipl-page-links span.dots { border: none; color: #94a3b8; }

/* ── Sidebar ────────────────────────────────────────────────────────────────── */
.aipl-sidebar { display: flex; flex-direction: column; gap: 14px; position: sticky; top: 32px; }
.aipl-scard {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.aipl-scard-hd {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; justify-content: space-between;
}
.aipl-scard-title {
    font-size: 12.5px; font-weight: 700; color: #0f172a;
    display: flex; align-items: center; gap: 6px;
}
.aipl-scard-live {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; color: #059669; font-weight: 600;
}
.aipl-scard-live-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #10b981;
    animation: aipl-pulse 1.4s ease-in-out infinite;
}
@keyframes aipl-pulse {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .5; transform: scale(.85); }
}
.aipl-scard-sub { font-size: 11px; color: #94a3b8; }

/* Real-time feed */
.aipl-rt-list { padding: 8px 0; }
.aipl-rt-item {
    display: flex; align-items: flex-start; gap: 10px;
    padding: 9px 16px; transition: background .1s;
}
.aipl-rt-item:hover { background: #f8fafc; }
.aipl-rt-dot {
    width: 8px; height: 8px; border-radius: 50%;
    margin-top: 4px; flex-shrink: 0;
}
.aipl-rt-body { flex: 1; min-width: 0; }
.aipl-rt-action { font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.aipl-rt-meta   { font-size: 11px; color: #94a3b8; margin-top: 1px; }

/* Quick stats */
.aipl-stats-list { padding: 8px 0; }
.aipl-stat-row {
    display: flex; align-items: center;
    padding: 8px 16px; gap: 8px; transition: background .1s;
}
.aipl-stat-row:hover { background: #f8fafc; }
.aipl-stat-icon {
    width: 28px; height: 28px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.aipl-stat-label  { flex: 1; font-size: 12px; color: #475569; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.aipl-stat-count  { font-size: 14px; font-weight: 800; color: #0f172a; }
.aipl-stat-trend  { font-size: 10.5px; font-weight: 700; white-space: nowrap; }
.aipl-stat-trend.up   { color: #10b981; }
.aipl-stat-trend.down { color: #ef4444; }
.aipl-stat-trend.flat { color: #94a3b8; }

/* Source donut */
.aipl-donut-wrap {
    padding: 16px;
    display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.aipl-donut-svg { width: 140px; height: 140px; }
.aipl-donut-legend { width: 100%; }
.aipl-legend-row {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 0;
    font-size: 12px; color: #475569;
    border-bottom: 1px solid #f1f5f9;
}
.aipl-legend-row:last-child { border-bottom: none; }
.aipl-legend-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.aipl-legend-label { flex: 1; }
.aipl-legend-pct  { font-weight: 700; color: #1e293b; }
.aipl-legend-cnt  { color: #94a3b8; font-size: 11px; }
</style>

<div class="aipl-wrap">

    <!-- ══ FILTER BAR (full-width) ═══════════════════════════════════════════════ -->
        <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" class="aipl-filter-bar">
            <input type="hidden" name="page"      value="mb-wphelper-ai">
            <input type="hidden" name="subtab"    value="ai-payment">
            <input type="hidden" name="aipay_tab" value="logs">

            <!-- Search -->
            <div class="aipl-search-wrap">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="apl_s" class="aipl-search-inp"
                       placeholder="<?php esc_attr_e( 'Tìm kiếm hoạt động, mã đơn hàng,...', 'whp' ); ?>"
                       value="<?php echo esc_attr( $search ); ?>">
            </div>

            <!-- Action filter — custom dropdown -->
            <?php
            $act_dot_color = isset( $actions_def[ $f_action ] ) ? $actions_def[ $f_action ][1] : '#94a3b8';
            $act_label     = $f_action && isset( $actions_def[ $f_action ] ) ? $actions_def[ $f_action ][0] : __( 'Tất cả hành động', 'whp' );
            ?>
            <div class="aipl-csel" id="aipl-csel-act">
                <input type="hidden" name="apl_act" value="<?php echo esc_attr( $f_action ); ?>">
                <button type="button" class="aipl-csel-btn">
                    <span class="aipl-csel-dot" style="background:<?php echo esc_attr( $act_dot_color ); ?>;"></span>
                    <span class="aipl-csel-lbl"><?php echo esc_html( $act_label ); ?></span>
                    <svg class="aipl-csel-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="aipl-csel-menu">
                    <div class="aipl-csel-opt<?php echo $f_action === '' ? ' selected' : ''; ?>" data-value="" data-color="#94a3b8">
                        <span class="aipl-csel-opt-dot" style="background:#94a3b8;"></span>
                        <span class="aipl-csel-opt-label"><?php esc_html_e( 'Tất cả hành động', 'whp' ); ?></span>
                        <svg class="aipl-csel-opt-check" width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <?php foreach ( $actions_def as $ak => $av ) : ?>
                    <div class="aipl-csel-opt<?php echo $f_action === $ak ? ' selected' : ''; ?>"
                         data-value="<?php echo esc_attr( $ak ); ?>"
                         data-color="<?php echo esc_attr( $av[1] ); ?>">
                        <span class="aipl-csel-opt-dot" style="background:<?php echo esc_attr( $av[1] ); ?>;"></span>
                        <span class="aipl-csel-opt-label"><?php echo esc_html( $av[0] ); ?></span>
                        <svg class="aipl-csel-opt-check" width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- User type filter — custom dropdown -->
            <?php
            $usr_opts      = [ '' => [ __( 'Tất cả người dùng', 'whp' ), '#94a3b8' ], 'ai' => [ 'AI System', '#8b5cf6' ], 'customer' => [ __( 'Khách hàng', 'whp' ), '#0ea5e9' ] ];
            $usr_dot_color = $usr_opts[ $f_user_t ][1] ?? '#94a3b8';
            $usr_label     = $usr_opts[ $f_user_t ][0] ?? __( 'Tất cả người dùng', 'whp' );
            ?>
            <div class="aipl-csel" id="aipl-csel-usr">
                <input type="hidden" name="apl_usr" value="<?php echo esc_attr( $f_user_t ); ?>">
                <button type="button" class="aipl-csel-btn">
                    <span class="aipl-csel-dot" style="background:<?php echo esc_attr( $usr_dot_color ); ?>;"></span>
                    <span class="aipl-csel-lbl"><?php echo esc_html( $usr_label ); ?></span>
                    <svg class="aipl-csel-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="aipl-csel-menu">
                    <?php foreach ( $usr_opts as $uv => $ud ) : ?>
                    <div class="aipl-csel-opt<?php echo $f_user_t === $uv ? ' selected' : ''; ?>"
                         data-value="<?php echo esc_attr( $uv ); ?>"
                         data-color="<?php echo esc_attr( $ud[1] ); ?>">
                        <span class="aipl-csel-opt-dot" style="background:<?php echo esc_attr( $ud[1] ); ?>;"></span>
                        <span class="aipl-csel-opt-label"><?php echo esc_html( $ud[0] ); ?></span>
                        <svg class="aipl-csel-opt-check" width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Source filter — custom dropdown -->
            <?php
            $src_dot_color = isset( $sources_def[ $f_source ] ) ? $sources_def[ $f_source ][1] : '#94a3b8';
            $src_label     = $f_source && isset( $sources_def[ $f_source ] ) ? $sources_def[ $f_source ][0] : __( 'Tất cả nguồn', 'whp' );
            ?>
            <div class="aipl-csel" id="aipl-csel-src">
                <input type="hidden" name="apl_src" value="<?php echo esc_attr( $f_source ); ?>">
                <button type="button" class="aipl-csel-btn">
                    <span class="aipl-csel-dot" style="background:<?php echo esc_attr( $src_dot_color ); ?>;"></span>
                    <span class="aipl-csel-lbl"><?php echo esc_html( $src_label ); ?></span>
                    <svg class="aipl-csel-chevron" width="11" height="11" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </button>
                <div class="aipl-csel-menu">
                    <div class="aipl-csel-opt<?php echo $f_source === '' ? ' selected' : ''; ?>" data-value="" data-color="#94a3b8">
                        <span class="aipl-csel-opt-dot" style="background:#94a3b8;"></span>
                        <span class="aipl-csel-opt-label"><?php esc_html_e( 'Tất cả nguồn', 'whp' ); ?></span>
                        <svg class="aipl-csel-opt-check" width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <?php foreach ( $sources_def as $sk => $sv ) : ?>
                    <div class="aipl-csel-opt<?php echo $f_source === $sk ? ' selected' : ''; ?>"
                         data-value="<?php echo esc_attr( $sk ); ?>"
                         data-color="<?php echo esc_attr( $sv[1] ); ?>">
                        <span class="aipl-csel-opt-dot" style="background:<?php echo esc_attr( $sv[1] ); ?>;"></span>
                        <span class="aipl-csel-opt-label"><?php echo esc_html( $sv[0] ); ?></span>
                        <svg class="aipl-csel-opt-check" width="12" height="12" viewBox="0 0 24 24" fill="none"
                             stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Date range -->
            <div class="aipl-daterange">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <input type="date" name="apl_from" class="aipl-date-inp"
                       value="<?php echo esc_attr( $f_from ); ?>" placeholder="<?php esc_attr_e( 'Từ ngày', 'whp' ); ?>">
                <span class="aipl-daterange-sep">—</span>
                <input type="date" name="apl_to" class="aipl-date-inp"
                       value="<?php echo esc_attr( $f_to ); ?>" placeholder="<?php esc_attr_e( 'Đến ngày', 'whp' ); ?>">
            </div>

            <!-- Submit -->
            <button type="submit" class="aipl-btn aipl-btn-primary">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                </svg>
                <?php esc_html_e( 'Bộ lọc', 'whp' ); ?>
            </button>

            <!-- Refresh -->
            <a href="<?php echo esc_url( $base_url ); ?>" class="aipl-btn-refresh" title="<?php esc_attr_e( 'Làm mới', 'whp' ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
            </a>

            <!-- Reset filters -->
            <?php if ( $has_filters ) : ?>
            <a href="<?php echo esc_url( $base_url ); ?>" class="aipl-btn-reset" title="<?php esc_attr_e( 'Xóa bộ lọc', 'whp' ); ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6"  y1="6" x2="18" y2="18"/>
                </svg>
            </a>
            <?php endif; ?>
        </form>

    <!-- ══ BODY: main + sidebar ══════════════════════════════════════════════════ -->
    <div class="aipl-body">

    <!-- ── Main column ─────────────────────────────────────────────────────────── -->
    <div class="aipl-main">

        <!-- ── Table card ──────────────────────────────────────────────────────── -->
        <div class="aipl-card">

            <div class="aipl-card-head">
                <h3>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <?php esc_html_e( 'Danh sách nhật ký', 'whp' ); ?>
                    <?php if ( $total > 0 ) : ?>
                    <span class="aipl-count-badge"><?php echo number_format( $total ); ?></span>
                    <?php endif; ?>
                </h3>
                <?php if ( $total > 0 ) : ?>
                <span class="aipl-head-meta">
                    <?php esc_html_e( 'Hiển thị', 'whp' ); ?> <?php echo $from_num; ?>–<?php echo $to_num; ?> / <?php echo number_format( $total ); ?> <?php esc_html_e( 'bản ghi', 'whp' ); ?>
                </span>
                <?php endif; ?>
            </div>

            <?php if ( empty( $page_rows ) ) : ?>

            <!-- Empty state -->
            <div class="aipl-empty">
                <div class="aipl-empty-icon">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                         stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <h3><?php echo $has_filters ? esc_html__( 'Không tìm thấy kết quả', 'whp' ) : esc_html__( 'Chưa có nhật ký', 'whp' ); ?></h3>
                <p>
                    <?php if ( $has_filters ) : ?>
                    <?php esc_html_e( 'Không có bản ghi nào khớp với bộ lọc hiện tại.', 'whp' ); ?>
                    <a href="<?php echo esc_url( $base_url ); ?>" style="color:#059669;"><?php esc_html_e( 'Xem tất cả', 'whp' ); ?></a>
                    <?php else : ?>
                    <?php esc_html_e( 'Khi khách hàng xác nhận chuyển khoản, hoạt động sẽ hiển thị ở đây.', 'whp' ); ?>
                    <?php endif; ?>
                </p>
            </div>

            <?php else : ?>

            <!-- Data table -->
            <div class="aipl-table-wrap">
                <table class="aipl-table">
                    <thead>
                        <tr>
                            <th>
                                <a href="<?php echo esc_url( add_query_arg( [ 'apl_sort' => $sort_dir === 'asc' ? 'desc' : 'asc', 'apl_page' => 1 ], $paginate_base ) ); ?>"
                                   style="text-decoration:none;color:inherit;display:inline-flex;align-items:center;gap:3px;">
                                    <?php esc_html_e( 'THỜI GIAN', 'whp' ); ?>
                                    <span class="aipl-sort-arrow active">
                                        <?php echo $sort_dir === 'asc' ? '↑' : '↓'; ?>
                                    </span>
                                </a>
                            </th>
                            <th><?php esc_html_e( 'HÀNH ĐỘNG', 'whp' ); ?></th>
                            <th><?php esc_html_e( 'NỘI DUNG', 'whp' ); ?></th>
                            <th><?php esc_html_e( 'ĐỐI TƯỢNG', 'whp' ); ?></th>
                            <th><?php esc_html_e( 'NGƯỜI DÙNG', 'whp' ); ?></th>
                            <th><?php esc_html_e( 'NGUỒN', 'whp' ); ?></th>
                            <th style="text-align:center;"><?php esc_html_e( 'CHI TIẾT', 'whp' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ( $page_rows as $row ) :
                        $adef = $actions_def[ $row['action'] ] ?? [ __( 'Hoạt động', 'whp' ), '#64748b', '#f8fafc' ];
                        $sdef = $sources_def[ $row['source'] ] ?? [ $row['source'], '#64748b' ];
                    ?>
                    <tr>
                        <!-- Thời gian -->
                        <td class="aipl-time">
                            <?php echo esc_html( date_i18n( 'd/m/Y', $row['time'] ) ); ?>
                            <br>
                            <span style="color:#94a3b8;font-size:10.5px;">
                                <?php echo esc_html( date_i18n( 'H:i:s', $row['time'] ) ); ?>
                            </span>
                        </td>

                        <!-- Hành động -->
                        <td>
                            <span class="aipl-action-badge"
                                  style="color:<?php echo esc_attr( $adef[0] === 'Xác minh thanh toán' ? '#059669' : $adef[1] ); ?>;background:<?php echo esc_attr( $adef[2] ); ?>;">
                                <span class="aipl-action-dot"
                                      style="background:<?php echo esc_attr( $adef[1] ); ?>;"></span>
                                <?php echo esc_html( $adef[0] ); ?>
                            </span>
                        </td>

                        <!-- Nội dung -->
                        <td class="aipl-content">
                            <?php echo esc_html( $row['content'] ); ?>
                        </td>

                        <!-- Đối tượng -->
                        <td>
                            <a href="<?php echo esc_url( $row['object_url'] ); ?>"
                               class="aipl-object-link" target="_blank">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                                <?php echo esc_html( $row['object'] ); ?>
                            </a>
                        </td>

                        <!-- Người dùng -->
                        <td>
                            <div class="aipl-user"><?php echo esc_html( $row['user_label'] ); ?></div>
                            <div class="aipl-user-sub">
                                <?php echo $row['user_type'] === 'ai' ? 'AI System' : esc_html__( 'Khách hàng', 'whp' ); ?>
                            </div>
                        </td>

                        <!-- Nguồn -->
                        <td>
                            <span class="aipl-src-badge">
                                <span class="aipl-src-dot" style="background:<?php echo esc_attr( $sdef[1] ); ?>;"></span>
                                <?php echo esc_html( $sdef[0] ); ?>
                            </span>
                        </td>

                        <!-- Chi tiết -->
                        <td style="text-align:center;">
                            <a href="<?php echo esc_url( $row['object_url'] ); ?>"
                               class="aipl-detail-btn" target="_blank" title="<?php echo esc_attr( __( 'Xem chi tiết đơn', 'whp' ) . ' ' . $row['object'] ); ?>">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ( $log_pages > 1 ) : ?>
            <div class="aipl-pagination">
                <span><?php esc_html_e( 'Hiển thị', 'whp' ); ?> <?php echo $from_num; ?>–<?php echo $to_num; ?> <?php esc_html_e( 'của', 'whp' ); ?> <?php echo number_format( $total ); ?> <?php esc_html_e( 'bản ghi', 'whp' ); ?></span>
                <div class="aipl-page-links">
                    <?php if ( $cur_page > 1 ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'apl_page', $cur_page - 1, $paginate_base ) ); ?>">‹ <?php esc_html_e( 'Trước', 'whp' ); ?></a>
                    <?php endif; ?>
                    <?php
                    $range     = 2;
                    $show_dots = false;
                    for ( $p = 1; $p <= $log_pages; $p++ ) :
                        if ( $p === 1 || $p === $log_pages || ( $p >= $cur_page - $range && $p <= $cur_page + $range ) ) :
                            $show_dots = false;
                            if ( $p === $cur_page ) :
                    ?>
                    <span class="current"><?php echo $p; ?></span>
                    <?php       else : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'apl_page', $p, $paginate_base ) ); ?>"><?php echo $p; ?></a>
                    <?php       endif;
                        elseif ( ! $show_dots ) :
                            $show_dots = true; ?>
                    <span class="dots">…</span>
                    <?php   endif;
                    endfor; ?>
                    <?php if ( $cur_page < $log_pages ) : ?>
                    <a href="<?php echo esc_url( add_query_arg( 'apl_page', $cur_page + 1, $paginate_base ) ); ?>"><?php esc_html_e( 'Sau', 'whp' ); ?> ›</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; // end rows check ?>

        </div><!-- /.aipl-card -->

    </div><!-- /.aipl-main -->

    <!-- ══ SIDEBAR ═══════════════════════════════════════════════════════════════ -->
    <div class="aipl-sidebar">

        <!-- Real-time feed -->
        <div class="aipl-scard">
            <div class="aipl-scard-hd">
                <span class="aipl-scard-title">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                    </svg>
                    <?php esc_html_e( 'Hoạt động gần đây', 'whp' ); ?>
                </span>
                <span class="aipl-scard-live">
                    <span class="aipl-scard-live-dot"></span><?php esc_html_e( 'Trực tiếp', 'whp' ); ?>
                </span>
            </div>
            <div class="aipl-rt-list">
                <?php if ( empty( $rt_feed ) ) : ?>
                <div style="padding:20px 16px;text-align:center;color:#94a3b8;font-size:12px;"><?php esc_html_e( 'Chưa có hoạt động', 'whp' ); ?></div>
                <?php else : foreach ( $rt_feed as $rf ) :
                    $rfdef = $actions_def[ $rf['action'] ] ?? [ '—', '#94a3b8', '#f8fafc' ];
                ?>
                <div class="aipl-rt-item">
                    <span class="aipl-rt-dot" style="background:<?php echo esc_attr( $rfdef[1] ); ?>;"></span>
                    <div class="aipl-rt-body">
                        <div class="aipl-rt-action"><?php echo esc_html( $rfdef[0] ); ?></div>
                        <div class="aipl-rt-meta">
                            <?php echo esc_html( $rf['object'] ); ?> ·
                            <?php echo esc_html( aipl_time_ago( $rf['time'] ) ); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- Quick stats (today) -->
        <div class="aipl-scard">
            <div class="aipl-scard-hd">
                <span class="aipl-scard-title">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6"  y1="20" x2="6"  y2="14"/>
                    </svg>
                    <?php esc_html_e( 'Thống kê nhanh', 'whp' ); ?>
                </span>
                <span class="aipl-scard-sub"><?php esc_html_e( 'Hôm nay', 'whp' ); ?></span>
            </div>
            <div class="aipl-stats-list">
                <!-- Total today -->
                <?php
                $trend_total = $yest_count > 0
                    ? round( ( $today_count - $yest_count ) / $yest_count * 100 )
                    : ( $today_count > 0 ? 100 : 0 );
                $trend_class = $trend_total > 0 ? 'up' : ( $trend_total < 0 ? 'down' : 'flat' );
                ?>
                <div class="aipl-stat-row">
                    <div class="aipl-stat-icon" style="background:#ecfdf5;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                             stroke="#059669" stroke-width="2" stroke-linecap="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    </div>
                    <span class="aipl-stat-label"><?php esc_html_e( 'Tổng hoạt động', 'whp' ); ?></span>
                    <span class="aipl-stat-count"><?php echo $today_count; ?></span>
                    <span class="aipl-stat-trend <?php echo $trend_class; ?>">
                        <?php echo $trend_total >= 0 ? '+' : ''; ?><?php echo $trend_total; ?>%
                    </span>
                </div>

                <!-- Per action type (top 5) -->
                <?php
                $act_today = [];
                foreach ( $all_entries as $e ) {
                    if ( $e['time'] >= $today_ts ) {
                        $act_today[ $e['action'] ] = ( $act_today[ $e['action'] ] ?? 0 ) + 1;
                    }
                }
                arsort( $act_today );
                $displayed = 0;
                foreach ( $act_today as $ak => $ac ) :
                    if ( $displayed >= 4 ) break;
                    $adef_s = $actions_def[ $ak ] ?? [ $ak, '#64748b', '#f8fafc' ];
                    $displayed++;
                ?>
                <div class="aipl-stat-row">
                    <div class="aipl-stat-icon" style="background:<?php echo esc_attr( $adef_s[2] ); ?>;">
                        <span style="width:8px;height:8px;border-radius:50%;background:<?php echo esc_attr( $adef_s[1] ); ?>;display:inline-block;"></span>
                    </div>
                    <span class="aipl-stat-label"><?php echo esc_html( $adef_s[0] ); ?></span>
                    <span class="aipl-stat-count"><?php echo $ac; ?></span>
                </div>
                <?php endforeach; ?>

                <?php if ( $today_count === 0 ) : ?>
                <div style="padding:12px 16px;text-align:center;color:#94a3b8;font-size:12px;"><?php esc_html_e( 'Chưa có hoạt động hôm nay', 'whp' ); ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Source distribution donut -->
        <div class="aipl-scard">
            <div class="aipl-scard-hd">
                <span class="aipl-scard-title">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                    </svg>
                    <?php esc_html_e( 'Hoạt động theo nguồn', 'whp' ); ?>
                </span>
            </div>
            <div class="aipl-donut-wrap">
                <?php if ( $src_total_all > 0 ) : ?>
                <!-- SVG Donut chart -->
                <svg class="aipl-donut-svg" viewBox="0 0 120 120">
                    <!-- Background track -->
                    <circle cx="60" cy="60" r="<?php echo $donut_r; ?>"
                            fill="none" stroke="#f1f5f9" stroke-width="18"/>
                    <?php foreach ( $donut_segs as $seg ) :
                        if ( $seg['pct'] <= 0 ) continue;
                    ?>
                    <circle cx="60" cy="60" r="<?php echo $donut_r; ?>"
                            fill="none"
                            stroke="<?php echo esc_attr( $seg['color'] ); ?>"
                            stroke-width="18"
                            stroke-dasharray="<?php echo round( $seg['len'], 2 ); ?> <?php echo round( $seg['gap'], 2 ); ?>"
                            stroke-dashoffset="<?php echo round( $seg['offset'] - $circumf * 0.25, 2 ); ?>"
                            stroke-linecap="butt"/>
                    <?php endforeach; ?>
                    <!-- Center text -->
                    <text x="60" y="56" text-anchor="middle"
                          font-size="18" font-weight="800" fill="#0f172a"
                          font-family="inherit"><?php echo $src_total_all; ?></text>
                    <text x="60" y="70" text-anchor="middle"
                          font-size="9" fill="#94a3b8" font-family="inherit"><?php esc_html_e( 'bản ghi', 'whp' ); ?></text>
                </svg>

                <!-- Legend -->
                <div class="aipl-donut-legend">
                    <?php foreach ( $donut_segs as $seg ) :
                        if ( $seg['count'] <= 0 ) continue;
                    ?>
                    <div class="aipl-legend-row">
                        <span class="aipl-legend-dot" style="background:<?php echo esc_attr( $seg['color'] ); ?>;"></span>
                        <span class="aipl-legend-label"><?php echo esc_html( $seg['label'] ); ?></span>
                        <span class="aipl-legend-pct"><?php echo round( $seg['pct'] * 100 ); ?>%</span>
                        <span class="aipl-legend-cnt">(<?php echo $seg['count']; ?>)</span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php else : ?>
                <div style="padding:20px 0;text-align:center;color:#94a3b8;font-size:12px;"><?php esc_html_e( 'Chưa có dữ liệu', 'whp' ); ?></div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /.aipl-sidebar -->

    </div><!-- /.aipl-body -->

</div><!-- /.aipl-wrap -->

<script>
(function () {
    var csels = document.querySelectorAll('.aipl-csel');

    csels.forEach(function (csel) {
        var btn   = csel.querySelector('.aipl-csel-btn');
        var menu  = csel.querySelector('.aipl-csel-menu');
        var inp   = csel.querySelector('input[type=hidden]');
        var dot   = csel.querySelector('.aipl-csel-dot');
        var lbl   = csel.querySelector('.aipl-csel-lbl');
        var opts  = csel.querySelectorAll('.aipl-csel-opt');

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = csel.classList.contains('open');
            // close all others
            document.querySelectorAll('.aipl-csel.open').forEach(function (o) { o.classList.remove('open'); });
            if (!isOpen) csel.classList.add('open');
        });

        opts.forEach(function (opt) {
            opt.addEventListener('click', function () {
                var val   = opt.dataset.value;
                var color = opt.dataset.color || '#94a3b8';
                var text  = opt.querySelector('.aipl-csel-opt-label').textContent;

                inp.value          = val;
                dot.style.background = color;
                lbl.textContent    = text;

                opts.forEach(function (o) { o.classList.remove('selected'); });
                opt.classList.add('selected');
                csel.classList.remove('open');
            });
        });
    });

    // Close on outside click
    document.addEventListener('click', function () {
        document.querySelectorAll('.aipl-csel.open').forEach(function (o) { o.classList.remove('open'); });
    });
})();
</script>

<?php } // end wpaap_aipay_logs_layout
