<?php defined('ABSPATH') || exit;

function wpaap_aipay_verify_hero( $pending_count = 0 ) {
    // Integrated into list panel header
}

function wpaap_aipay_verify_layout() {
    $f_status = sanitize_text_field( $_GET['aipv_status'] ?? '' );
    $allowed_statuses = [ '', 'pending', 'valid', 'suspicious', 'invalid' ];
    if ( ! in_array( $f_status, $allowed_statuses, true ) ) $f_status = '';

    $pending_orders = [];
    if ( function_exists( 'wc_get_orders' ) ) {
        if ( $f_status === 'pending' || $f_status === '' ) {
            // "Chờ xác minh": no AI result yet
            $base_meta = [
                'relation' => 'AND',
                [ 'key' => '_whp_transfer_confirmed_at', 'compare' => 'EXISTS' ],
            ];
            if ( $f_status === 'pending' ) {
                $base_meta[] = [
                    'relation' => 'OR',
                    [ 'key' => '_whp_ai_verify_result', 'compare' => 'NOT EXISTS' ],
                    [ 'key' => '_whp_ai_verify_result', 'value' => '', 'compare' => '=' ],
                ];
            }
            $pending_orders = wc_get_orders( [
                'status'     => [ 'on-hold', 'pending', 'processing', 'completed', 'wc-on-hold', 'wc-pending', 'wc-processing', 'wc-completed' ],
                'meta_query' => $base_meta,
                'limit'      => 100,
                'orderby'    => 'date',
                'order'      => 'DESC',
            ] );
        } else {
            // "Đã xác minh", "Nghi ngờ", "Rủi ro cao": fetch all with result, then PHP-filter by verdict
            $raw_orders = wc_get_orders( [
                'status'     => [ 'on-hold', 'pending', 'processing', 'completed', 'wc-on-hold', 'wc-pending', 'wc-processing', 'wc-completed' ],
                'meta_query' => [
                    'relation' => 'AND',
                    [ 'key' => '_whp_transfer_confirmed_at', 'compare' => 'EXISTS' ],
                    [ 'key' => '_whp_ai_verify_result', 'compare' => 'EXISTS' ],
                    [ 'key' => '_whp_ai_verify_result', 'value' => '', 'compare' => '!=' ],
                ],
                'limit'      => 200,
                'orderby'    => 'date',
                'order'      => 'DESC',
            ] );
            foreach ( $raw_orders as $ro ) {
                $ai_r = $ro->get_meta( '_whp_ai_verify_result' );
                $v    = is_array( $ai_r ) ? ( $ai_r['verdict'] ?? '' ) : '';
                if ( $v === $f_status ) {
                    $pending_orders[] = $ro;
                }
            }
        }
    }

    $ai_provider = '';
    foreach ( [ 'google' => 'Gemini', 'openai' => 'GPT-4o', 'anthropic' => 'Claude' ] as $p => $lbl ) {
        if ( function_exists( 'wpaap_is_provider_connected' ) && wpaap_is_provider_connected( $p ) ) {
            $ai_provider = $p;
            break;
        }
    }
    ?>
<style>
/* ============================================================
   VERIFY TAB — 3-COLUMN LAYOUT
   ============================================================ */
.aipv-wrap { max-width: 1280px; margin: 0 auto 48px; }

.aipv-no-ai {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px;
    padding: 14px 18px; display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px;
}

/* 3-col grid */
.aipv-layout {
    display: grid;
    grid-template-columns: 260px 1fr 216px;
    gap: 14px;
    align-items: start;
}

/* ── LIST COLUMN ──────────────────────────────────────────── */
.aipv-list-col {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex; flex-direction: column;
}
.aipv-list-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 14px; border-bottom: 1px solid #f1f5f9;
    font-size: 12.5px; font-weight: 700; color: #0f172a; background: #fafafa;
}
.aipv-list-badge {
    background: #e11d48; color: #fff; font-size: 11px; font-weight: 700;
    border-radius: 20px; padding: 2px 8px;
}
.aipv-list-badge.zero { background: #e2e8f0; color: #64748b; }

.aipv-order-list { overflow-y: auto; max-height: 600px; }

.aipv-order-row {
    display: flex; align-items: flex-start; gap: 9px;
    padding: 10px 12px; border-bottom: 1px solid #f8fafc;
    cursor: pointer; transition: background .12s;
    border-left: 3px solid transparent;
}
.aipv-order-row:last-child { border-bottom: none; }
.aipv-order-row:hover { background: #f8fafc; }
.aipv-order-row.active { background: #fff7f7; border-left-color: #e11d48; }

.aipv-order-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: #475569; flex-shrink: 0; margin-top: 2px;
}
.aipv-order-info { flex: 1; min-width: 0; }
.aipv-order-row-top { display: flex; align-items: center; justify-content: space-between; gap: 4px; }
.aipv-order-num { font-size: 12px; font-weight: 700; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.aipv-order-time { font-size: 10.5px; color: #94a3b8; flex-shrink: 0; }
.aipv-order-method { font-size: 11px; color: #64748b; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.aipv-order-row-bottom { display: flex; align-items: center; justify-content: space-between; margin-top: 4px; }
.aipv-order-amount-small { font-size: 11.5px; font-weight: 700; color: #059669; }
.aipv-order-verdict {
    font-size: 10px; font-weight: 700; border-radius: 20px; padding: 2px 7px; white-space: nowrap;
}
.aipv-order-verdict.valid      { background: #d1fae5; color: #065f46; }
.aipv-order-verdict.suspicious { background: #fef3c7; color: #92400e; }
.aipv-order-verdict.invalid    { background: #fee2e2; color: #991b1b; }
.aipv-order-verdict.pending    { background: #f1f5f9; color: #94a3b8; }

.aipv-row-hidden { display: none; }
.aipv-list-footer {
    padding: 9px 14px; border-top: 1px solid #f1f5f9; text-align: center;
}
.aipv-list-footer a { font-size: 12px; color: #e11d48; text-decoration: none; font-weight: 600; }
.aipv-list-footer a:hover { text-decoration: underline; }
.aipv-load-more-btn {
    width: 100%; padding: 7px 12px; border: 1px solid #e2e8f0; border-radius: 8px;
    background: #fff; color: #e11d48; font-size: 12px; font-weight: 600; cursor: pointer;
    transition: background .12s;
}
.aipv-load-more-btn:hover { background: #fff7f7; }

.aipv-empty { padding: 32px 18px; text-align: center; }
.aipv-empty-icon { font-size: 30px; margin-bottom: 9px; }
.aipv-empty-title { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 5px; }
.aipv-empty-desc  { font-size: 11.5px; color: #64748b; line-height: 1.55; }

/* ── DETAIL COLUMN ────────────────────────────────────────── */
.aipv-detail-col {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04); min-height: 500px;
}

.aipv-placeholder {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 460px; color: #94a3b8; text-align: center; padding: 40px;
}
.aipv-placeholder svg { margin-bottom: 14px; opacity: .35; }
.aipv-placeholder p { font-size: 13px; line-height: 1.6; }

/* Risk banner */
.aipv-risk-banner {
    display: none; align-items: center; gap: 10px;
    padding: 8px 16px; border-bottom: 1px solid #fecdd3; background: #fff1f2;
}
.aipv-risk-banner-badge { font-size: 12px; font-weight: 700; white-space: nowrap; }
.aipv-risk-banner-badge.high { color: #991b1b; }
.aipv-risk-banner-badge.mid  { color: #92400e; }
.aipv-risk-banner-badge.low  { color: #065f46; }
.aipv-risk-banner.mid  { background: #fffbeb; border-bottom-color: #fde68a; }
.aipv-risk-banner.low  { background: #f0fdf4; border-bottom-color: #bbf7d0; }
.aipv-risk-banner-label { font-size: 12px; color: #475569; white-space: nowrap; }
.aipv-risk-banner-bar { flex: 1; height: 6px; background: #fecdd3; border-radius: 6px; overflow: hidden; }
.aipv-risk-banner.mid  .aipv-risk-banner-bar { background: #fde68a; }
.aipv-risk-banner.low  .aipv-risk-banner-bar { background: #bbf7d0; }
.aipv-risk-banner-fill { height: 100%; border-radius: 6px; transition: width .6s ease; }
.aipv-risk-banner-fill.high { background: #ef4444; }
.aipv-risk-banner-fill.mid  { background: #f59e0b; }
.aipv-risk-banner-fill.low  { background: #10b981; }

/* Detail title */
.aipv-detail-title { padding: 13px 16px 10px; border-bottom: 1px solid #f1f5f9; }
.aipv-detail-title h2 { margin: 0; font-size: 17px; font-weight: 700; color: #0f172a; }

/* 4-col meta */
.aipv-detail-meta {
    display: grid; grid-template-columns: repeat(4, 1fr);
    border-bottom: 1px solid #f1f5f9;
}
.aipv-meta-item { padding: 10px 14px; border-right: 1px solid #f1f5f9; }
.aipv-meta-item:last-child { border-right: none; }
.aipv-meta-label { font-size: 10.5px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; }
.aipv-meta-val { font-size: 12.5px; font-weight: 600; color: #0f172a; }
.aipv-meta-val.amount { color: #059669; }

/* 2-col body */
.aipv-detail-body { display: grid; grid-template-columns: 1fr 1fr; align-items: start; }
.aipv-receipt-panel { padding: 14px; border-right: 1px solid #f1f5f9; }
.aipv-ai-panel { padding: 14px; }

.aipv-panel-heading {
    font-size: 12px; font-weight: 700; color: #374151;
    margin-bottom: 10px; padding-bottom: 7px; border-bottom: 1px solid #f1f5f9;
}

/* Receipt */
.aipv-receipt-img {
    width: 100%; max-height: 260px; object-fit: contain; border-radius: 10px;
    border: 1px solid #e2e8f0; background: #f8fafc; display: block; cursor: zoom-in;
}
.aipv-receipt-placeholder {
    min-height: 140px; background: #f8fafc; border-radius: 10px; border: 2px dashed #e2e8f0;
    display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 12px; gap: 7px;
}
.aipv-receipt-actions { display: flex; gap: 6px; margin-top: 8px; }
.aipv-receipt-action-btn {
    flex: 1; padding: 6px 4px; border: 1px solid #e2e8f0; border-radius: 7px; background: #fff;
    font-size: 11px; color: #475569; cursor: pointer; text-align: center; text-decoration: none;
    display: flex; align-items: center; justify-content: center; gap: 4px; transition: background .1s;
}
.aipv-receipt-action-btn:hover { background: #f1f5f9; color: #0f172a; }

/* Verify button */
.aipv-btn-verify {
    width: 100%; margin-top: 10px; padding: 10px; border: none; border-radius: 9px; cursor: pointer;
    font-size: 13px; font-weight: 700; display: flex; align-items: center; justify-content: center; gap: 7px;
    background: linear-gradient(135deg, #e11d48, #be123c);
    color: #fff; box-shadow: 0 3px 12px rgba(225,29,72,.2); transition: opacity .15s;
}
.aipv-btn-verify:hover { opacity: .9; }
.aipv-btn-verify:disabled { opacity: .5; cursor: not-allowed; }

/* Progress */
.aipv-progress-wrap { display: none; padding: 10px 0 2px; }
.aipv-progress-label { font-size: 11px; color: #475569; margin-bottom: 5px; display: flex; align-items: center; gap: 5px; }
.aipv-progress-label-dot { width: 6px; height: 6px; border-radius: 50%; background: #e11d48; animation: aipv-pulse 1.2s infinite; }
.aipv-progress-track { height: 5px; background: #e2e8f0; border-radius: 6px; overflow: hidden; margin-bottom: 3px; }
.aipv-progress-fill { height: 100%; width: 0%; background: linear-gradient(90deg, #e11d48, #f43f5e); border-radius: 6px; transition: width .35s ease; }
.aipv-progress-pct { font-size: 10.5px; font-weight: 700; color: #e11d48; text-align: right; }
.aipv-progress-steps { display: flex; gap: 3px; margin-top: 7px; justify-content: space-between; }
.aipv-progress-step { font-size: 10px; color: #94a3b8; }
.aipv-progress-step.active { color: #e11d48; font-weight: 600; }
.aipv-progress-step.done   { color: #10b981; }

/* Checklist */
.aipv-checklist { display: flex; flex-direction: column; gap: 8px; }
.aipv-check-item { display: flex; align-items: center; gap: 8px; font-size: 12.5px; }
.aipv-check-icon { width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0; }
.aipv-check-icon.pass { background: #d1fae5; color: #059669; }
.aipv-check-icon.fail { background: #fee2e2; color: #dc2626; }
.aipv-check-label { flex: 1; color: #374151; }
.aipv-check-val { font-size: 11.5px; font-weight: 600; max-width: 110px; text-align: right; word-break: break-all; }
.aipv-check-val.pass { color: #059669; }
.aipv-check-val.fail { color: #dc2626; }

/* OCR confidence row */
.aipv-conf-item { display: flex; align-items: center; gap: 8px; padding-top: 8px; margin-top: 6px; border-top: 1px solid #f1f5f9; }
.aipv-conf-label { color: #374151; font-size: 12.5px; flex: 1; }
.aipv-conf-bar-wrap { width: 50px; height: 5px; background: #e2e8f0; border-radius: 6px; overflow: hidden; }
.aipv-conf-bar-fill { height: 100%; border-radius: 6px; background: #10b981; transition: width .6s ease; }
.aipv-conf-pct { font-size: 12px; font-weight: 700; color: #059669; min-width: 28px; text-align: right; }

/* Nhận định AI */
.aipv-verdict-section { margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; }
.aipv-verdict-text { font-size: 12px; color: #374151; line-height: 1.55; margin-bottom: 9px; }
.aipv-verdict-badge-bottom {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
}
.aipv-verdict-badge-bottom.valid      { background: #d1fae5; color: #065f46; }
.aipv-verdict-badge-bottom.suspicious { background: #fef3c7; color: #92400e; }
.aipv-verdict-badge-bottom.invalid    { background: #fee2e2; color: #991b1b; }

/* Bank notice — in verdict section */
.aipv-bank-notice {
    display: flex; align-items: flex-start; gap: 8px;
    margin-top: 10px; padding: 9px 11px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;
    font-size: 11.5px; color: #92400e; line-height: 1.5;
}
.aipv-bank-notice svg { color: #d97706; }

/* Bank notice card — sidebar */
.aipv-bank-notice-card {
    background: #fffbeb !important;
    border-color: #fde68a !important;
    padding: 12px 14px !important;
    display: flex; align-items: flex-start; gap: 10px;
}
.aipv-bnc-icon {
    width: 30px; height: 30px; border-radius: 8px;
    background: #fef3c7; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.aipv-bnc-body { flex: 1; min-width: 0; }
.aipv-bnc-title { font-size: 12px; font-weight: 700; color: #92400e; margin-bottom: 4px; }
.aipv-bnc-text { font-size: 11.5px; color: #78350f; line-height: 1.5; }

/* Lightbox */
.aipv-lightbox { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.85); z-index: 999999; align-items: center; justify-content: center; }
.aipv-lightbox.open { display: flex; }
.aipv-lightbox img { max-width: 90vw; max-height: 90vh; object-fit: contain; border-radius: 10px; }
.aipv-lightbox-close { position: absolute; top: 16px; right: 20px; color: #fff; font-size: 30px; cursor: pointer; background: none; border: none; line-height: 1; }

/* ── SIDEBAR COLUMN ───────────────────────────────────────── */
.aipv-sidebar-col { display: flex; flex-direction: column; gap: 12px; }
.aipv-sidebar-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04); display: none;
}
.aipv-sc-title {
    padding: 10px 13px; font-size: 12px; font-weight: 700; color: #0f172a;
    border-bottom: 1px solid #f1f5f9; background: #fafafa;
}
.aipv-act-btns { padding: 10px; display: flex; flex-direction: column; gap: 7px; }
.aipv-act-btn {
    width: 100%; padding: 8px 11px; border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; text-align: left; display: flex; align-items: center; gap: 6px; transition: opacity .15s;
}
.aipv-act-btn:hover { opacity: .85; }
.aipv-act-btn.confirm { background: #22c55e; color: #fff; border: none; }
.aipv-act-btn.suspect { background: #f59e0b; color: #fff; border: none; }
.aipv-act-btn.reject  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.aipv-act-btn.outline { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
.aipv-note-area { padding: 0 10px 10px; display: none; }
.aipv-note-input {
    width: 100%; min-height: 60px; resize: vertical;
    border: 1px solid #d1d5db !important; border-radius: 8px !important;
    padding: 7px 9px; font-size: 12px; color: #0f172a; box-sizing: border-box; font-family: inherit;
}
.aipv-note-submit { margin-top: 5px; padding: 5px 12px; border: none; border-radius: 7px; background: #0f172a; color: #fff; font-size: 11.5px; font-weight: 600; cursor: pointer; }

/* Order info card */
.aipv-oi-rows { padding: 8px 12px; display: flex; flex-direction: column; gap: 7px; }
.aipv-oi-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 5px; font-size: 11.5px; }
.aipv-oi-key { color: #94a3b8; font-weight: 600; flex-shrink: 0; }
.aipv-oi-val { color: #0f172a; font-weight: 600; text-align: right; word-break: break-all; }
.aipv-oi-val.pending    { color: #d97706; }
.aipv-oi-val.processing { color: #059669; }
.aipv-oi-val.cancelled  { color: #dc2626; }
.aipv-oi-detail-link { display: block; text-align: center; padding: 7px 12px 10px; font-size: 12px; font-weight: 600; color: #e11d48; text-decoration: none; }
.aipv-oi-detail-link:hover { text-decoration: underline; }

/* Spinner / pulse */
@keyframes aipv-spin  { to { transform: rotate(360deg); } }
@keyframes aipv-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.aipv-spinner { animation: aipv-spin .8s linear infinite; }

/* ── FILTER BAR ───────────────────────────────────────────── */
.aipv-filter-bar {
    display: flex; align-items: center; gap: 8px;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
    padding: 10px 14px; margin-bottom: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.aipv-filter-search {
    flex: 1; display: flex; align-items: center; gap: 7px;
    background: #f8fafc; border: 1px solid #e2e8f0 !important;
    border-radius: 8px !important; padding: 10px 10px;
}
.aipv-filter-search input {
    flex: 1; border: none !important; background: none; outline: none;
    font-size: 12.5px; color: #0f172a; padding: 0 !important; margin: 0;
    box-shadow: none !important; border-radius: 0 !important; min-height: unset !important;
}
.aipv-filter-search input::placeholder { color: #94a3b8; }
.aipv-filter-search svg { flex-shrink: 0; color: #94a3b8; }
/* Custom status dropdown */
.aipv-filter-dropdown { position: relative; }
.aipv-filter-dd-trigger {
    display: flex; align-items: center; gap: 6px;
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px;
    font-size: 12.5px; color: #374151; background: #fff; cursor: pointer; user-select: none; white-space: nowrap;
}
.aipv-filter-dd-trigger svg { color: #94a3b8; transition: transform .15s; flex-shrink: 0; }
.aipv-filter-dd-trigger.open svg { transform: rotate(180deg); }
.aipv-filter-dd-menu {
    display: none; position: absolute; top: calc(100% + 5px); left: 0; z-index: 999;
    background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0,0,0,.1); min-width: 170px; overflow: hidden;
}
.aipv-filter-dd-menu.open { display: block; }
.aipv-filter-dd-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 12px; font-size: 12.5px; color: #374151; cursor: pointer; transition: background .1s;
}
.aipv-filter-dd-item:hover { background: #f8fafc; }
.aipv-filter-dd-item.selected { background: #fff7f7; color: #e11d48; font-weight: 600; }
.aipv-dd-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; transition: background .15s;
}
.aipv-filter-dd-item.selected .aipv-dd-dot { background: #22c55e; }
.aipv-filter-dd-item:not(.selected) .aipv-dd-dot { background: #ef4444; }
.aipv-dr-wrap{position:relative;}
.aipv-dr-trigger{display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 11px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;color:#374151;cursor:pointer;white-space:nowrap;transition:border-color .15s,color .15s;}
.aipv-dr-trigger:hover,.aipv-dr-trigger.open{border-color:#e11d48;color:#e11d48;}
.aipv-dr-popover{display:none;position:absolute;top:calc(100% + 7px);right:0;z-index:9999;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.13);padding:14px 16px;min-width:230px;}
.aipv-dr-popover.open{display:block;}
.aipv-dr-row{display:flex;flex-direction:column;gap:4px;margin-bottom:10px;}
.aipv-dr-row label{font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;}
.aipv-dr-row input[type="date"]{width:100%;height:32px;border:1px solid #e2e8f0!important;border-radius:7px!important;padding:0 8px!important;font-size:12px!important;color:#374151!important;background:#f8fafc!important;box-shadow:none!important;cursor:pointer;box-sizing:border-box;}
.aipv-dr-row input[type="date"]:focus{border-color:#e11d48!important;outline:none!important;}
.aipv-dr-actions{display:flex;gap:7px;margin-top:6px;}
.aipv-dr-apply{flex:1;padding:7px 12px;border:none;border-radius:7px;background:#e11d48;color:#fff;font-size:12px;font-weight:600;cursor:pointer;}
.aipv-dr-apply:hover{background:#be123c;}
.aipv-dr-clear{padding:7px 10px;border:1px solid #e2e8f0;border-radius:7px;background:#fff;color:#64748b;font-size:12px;cursor:pointer;}
.aipv-dr-clear:hover{background:#f8fafc;}
.aipv-filter-icon-btn {
    width: 32px; height: 32px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fff;
    display: flex; align-items: center; justify-content: center; cursor: pointer;
    color: #64748b; transition: background .1s; flex-shrink: 0;
}
.aipv-filter-icon-btn:hover { background: #f1f5f9; color: #0f172a; }
.aipv-filter-icon-btn.active { background: #fff1f2; border-color: #fecdd3; color: #e11d48; }

/* Responsive */
@media (max-width: 1060px) {
    .aipv-layout { grid-template-columns: 240px 1fr; }
    .aipv-sidebar-col { display: none; }
}
@media (max-width: 760px) {
    .aipv-layout { grid-template-columns: 1fr; }
    .aipv-detail-body { grid-template-columns: 1fr; }
    .aipv-receipt-panel { border-right: none; border-bottom: 1px solid #f1f5f9; }
    .aipv-detail-meta { grid-template-columns: 1fr 1fr; }
}
</style>

<?php if ( ! $ai_provider ) : ?>
<div class="aipv-no-ai">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <div>
        <div style="font-weight:700;color:#92400e;font-size:12.5px;margin-bottom:3px"><?php esc_html_e( 'Chưa kết nối AI', 'whp' ); ?></div>
        <div style="font-size:12px;color:#78350f;line-height:1.5"><?php esc_html_e( 'Cần API Key AI để xác minh biên lai.', 'whp' ); ?> <a href="<?php echo esc_url( admin_url( 'admin.php?page=mb-wphelper-ai&subtab=ai-connectors' ) ); ?>" style="color:#059669;font-weight:600"><?php esc_html_e( 'Cấu hình tại đây', 'whp' ); ?></a>.</div>
    </div>
</div>
<?php endif; ?>

<div class="aipv-wrap">

<!-- ── FILTER BAR ─────────────────────────────────────────────── -->
<div class="aipv-filter-bar">
    <div class="aipv-filter-search">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="aipv-filter-q" placeholder="<?php esc_attr_e( 'Tìm kiếm đơn hàng, tên, số tiền...', 'whp' ); ?>">
    </div>
    <?php
    $dd_options = [
        ''           => [ 'label' => __( 'Tất cả', 'whp' ), 'color' => '#94a3b8' ],
        'pending'    => [ 'label' => __( 'Chờ xác minh', 'whp' ), 'color' => '#f97316' ],
        'valid'      => [ 'label' => __( 'Đã xác minh', 'whp' ), 'color' => '#10b981' ],
        'suspicious' => [ 'label' => __( 'Nghi ngờ', 'whp' ), 'color' => '#f59e0b' ],
        'invalid'    => [ 'label' => __( 'Rủi ro cao', 'whp' ), 'color' => '#ef4444' ],
    ];
    $dd_active_label = $dd_options[ $f_status ]['label'] ?? __( 'Trạng thái', 'whp' );
    $verify_base_url = add_query_arg( [
        'page'    => 'mb-wphelper-ai',
        'subtab'  => 'ai-payment',
        'aipay_tab' => 'verify',
    ], admin_url( 'admin.php' ) );
    ?>
    <div class="aipv-filter-dropdown" id="aipv-filter-dropdown">
        <div class="aipv-filter-dd-trigger <?php echo $f_status ? 'active' : ''; ?>" id="aipv-filter-dd-trigger"
             style="<?php echo $f_status ? 'border-color:#e11d48;color:#e11d48;' : ''; ?>">
            <span class="aipv-dd-dot" style="background:<?php echo esc_attr( $dd_options[ $f_status ]['color'] ?? '#94a3b8' ); ?>;margin-right:2px"></span>
            <span id="aipv-filter-dd-label"><?php echo esc_html( $dd_active_label ); ?></span>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="aipv-filter-dd-menu" id="aipv-filter-dd-menu">
            <?php foreach ( $dd_options as $val => $opt ) : ?>
            <div class="aipv-filter-dd-item <?php echo $f_status === $val ? 'selected' : ''; ?>"
                 data-value="<?php echo esc_attr( $val ); ?>"
                 data-color="<?php echo esc_attr( $opt['color'] ); ?>"
                 data-url="<?php echo esc_attr( $val !== '' ? add_query_arg( 'aipv_status', $val, $verify_base_url ) : $verify_base_url ); ?>">
                <span class="aipv-dd-dot" style="background:<?php echo esc_attr( $opt['color'] ); ?>"></span>
                <?php echo esc_html( $opt['label'] ); ?>
                <?php if ( $f_status === $val ) : ?>
                <svg style="margin-left:auto;color:#e11d48" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="aipv-dr-wrap">
        <div class="aipv-dr-trigger" id="aipv-dr-trigger">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span id="aipv-dr-label"><?php esc_html_e( 'Tất cả ngày', 'whp' ); ?></span>
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
        <div class="aipv-dr-popover" id="aipv-dr-popover">
            <div class="aipv-dr-row">
                <label><?php esc_html_e( 'Từ ngày', 'whp' ); ?></label>
                <input type="date" id="aipv-filter-from">
            </div>
            <div class="aipv-dr-row">
                <label><?php esc_html_e( 'Đến ngày', 'whp' ); ?></label>
                <input type="date" id="aipv-filter-to">
            </div>
            <div class="aipv-dr-actions">
                <button type="button" class="aipv-dr-clear" id="aipv-dr-clear"><?php esc_html_e( 'Xóa', 'whp' ); ?></button>
                <button type="button" class="aipv-dr-apply" id="aipv-dr-apply"><?php esc_html_e( 'Áp dụng', 'whp' ); ?></button>
            </div>
        </div>
    </div>
    <button class="aipv-filter-icon-btn" id="aipv-filter-btn" title="<?php esc_attr_e( 'Lọc', 'whp' ); ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
    </button>
    <button class="aipv-filter-icon-btn" id="aipv-filter-reset" title="<?php esc_attr_e( 'Đặt lại', 'whp' ); ?>">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.63"/></svg>
    </button>
</div>

<div class="aipv-layout">

<!-- ── COL 1: ORDER LIST ─────────────────────────────────────── -->
<div class="aipv-list-col">
    <?php
    $list_titles = [
        ''           => __( 'Tất cả đơn chuyển khoản', 'whp' ),
        'pending'    => __( 'Chờ xác minh', 'whp' ),
        'valid'      => __( 'Đã xác minh', 'whp' ),
        'suspicious' => __( 'Nghi ngờ', 'whp' ),
        'invalid'    => __( 'Rủi ro cao', 'whp' ),
    ];
    ?>
    <div class="aipv-list-header">
        <span><?php echo esc_html( $list_titles[ $f_status ] ?? __( 'Danh sách đơn hàng', 'whp' ) ); ?></span>
        <span class="aipv-list-badge <?php echo empty( $pending_orders ) ? 'zero' : ''; ?>"><?php echo count( $pending_orders ); ?></span>
    </div>
    <div class="aipv-order-list">
        <?php if ( empty( $pending_orders ) ) : ?>
        <div class="aipv-empty">
            <?php if ( $f_status === 'pending' || $f_status === '' ) : ?>
            <div class="aipv-empty-icon">✅</div>
            <div class="aipv-empty-title"><?php echo $f_status === 'pending' ? esc_html__( 'Không có đơn chờ xác minh', 'whp' ) : esc_html__( 'Không có đơn chuyển khoản', 'whp' ); ?></div>
            <div class="aipv-empty-desc"><?php esc_html_e( 'Khi khách xác nhận chuyển khoản, đơn sẽ xuất hiện ở đây.', 'whp' ); ?></div>
            <?php else : ?>
            <div class="aipv-empty-icon">🔍</div>
            <div class="aipv-empty-title"><?php printf( esc_html__( 'Không có đơn %s', 'whp' ), esc_html( $list_titles[ $f_status ] ?? '' ) ); ?></div>
            <div class="aipv-empty-desc"><?php esc_html_e( 'Chưa có đơn hàng nào có trạng thái này.', 'whp' ); ?></div>
            <?php endif; ?>
        </div>
        <?php else : ?>
        <?php foreach ( $pending_orders as $aipv_idx => $order ) :
            $oid         = $order->get_id();
            $name        = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
            $phone       = $order->get_billing_phone();
            $email       = $order->get_billing_email();
            $total       = $order->get_total();
            $total_fmt   = number_format( $total, 0, ',', '.' ) . ' VND';
            $date        = $order->get_meta( '_whp_transfer_confirmed_at' );
            $date_f      = $date ? wp_date( 'd/m H:i', strtotime( $date . ' UTC' ) ) : '—';
            $time_f      = $date ? wp_date( 'H:i', strtotime( $date . ' UTC' ) )     : '—';
            $receipt     = $order->get_meta( '_whp_transfer_receipt' );
            $bank        = $order->get_meta( '_whp_transfer_bank' );
            $sender      = $order->get_meta( '_whp_transfer_sender' );
            $last4       = $order->get_meta( '_whp_transfer_last4' );
            $payment_ttl = $order->get_payment_method_title();
            $ai_res      = $order->get_meta( '_whp_ai_verify_result' );
            $verdict     = is_array( $ai_res ) ? ( $ai_res['verdict']    ?? '' ) : '';
            $risk_raw    = is_array( $ai_res ) ? ( $ai_res['risk_score'] ?? 0  ) : 0;
            $risk_pct    = (int) ( $risk_raw * 100 );
            $status_lbl  = wc_get_order_status_name( $order->get_status() );
            $order_url   = get_edit_post_link( $oid ) ?: admin_url( 'post.php?post=' . $oid . '&action=edit' );
            $method_disp = $bank ?: $payment_ttl ?: __( 'Chuyển khoản', 'whp' );
        ?>
        <div class="aipv-order-row<?php echo $aipv_idx >= 5 ? ' aipv-row-hidden' : ''; ?>"
             data-order-id="<?php echo $oid; ?>"
             data-name="<?php echo esc_attr( $name ); ?>"
             data-phone="<?php echo esc_attr( $phone ); ?>"
             data-email="<?php echo esc_attr( $email ); ?>"
             data-total="<?php echo esc_attr( $total ); ?>"
             data-total-fmt="<?php echo esc_attr( $total_fmt ); ?>"
             data-receipt="<?php echo esc_attr( $receipt ); ?>"
             data-bank="<?php echo esc_attr( $bank ); ?>"
             data-payment="<?php echo esc_attr( $method_disp ); ?>"
             data-sender="<?php echo esc_attr( $sender ); ?>"
             data-last4="<?php echo esc_attr( $last4 ); ?>"
             data-confirmed="<?php echo esc_attr( $date_f ); ?>"
             data-status="<?php echo esc_attr( $status_lbl ); ?>"
             data-status-slug="<?php echo esc_attr( $order->get_status() ); ?>"
             data-order-url="<?php echo esc_attr( $order_url ); ?>"
             data-ai-result="<?php echo esc_attr( is_array( $ai_res ) ? wp_json_encode( $ai_res ) : '' ); ?>"
        >
            <div class="aipv-order-avatar"><?php echo esc_html( strtoupper( mb_substr( $name ?: '#', 0, 1 ) ) ); ?></div>
            <div class="aipv-order-info">
                <div class="aipv-order-row-top">
                    <div class="aipv-order-num">#<?php echo $oid; ?> — <?php echo esc_html( $name ?: __( 'Khách', 'whp' ) ); ?></div>
                    <div class="aipv-order-time"><?php echo $time_f; ?></div>
                </div>
                <div class="aipv-order-method"><?php echo esc_html( $method_disp ); ?><?php echo $phone ? ' · ' . esc_html( $phone ) : ''; ?></div>
                <div class="aipv-order-row-bottom">
                    <div class="aipv-order-amount-small"><?php echo number_format( $total, 0, ',', '.' ); ?>đ</div>
                    <?php if ( $verdict ) : ?>
                    <div class="aipv-order-verdict <?php echo esc_attr( $verdict ); ?>">
                        <?php
                        if ( $verdict === 'valid'      ) echo '✓ ' . esc_html__( 'Đã xác minh', 'whp' ) . ( $risk_pct ? ' ' . $risk_pct . '%' : '' );
                        elseif ( $verdict === 'suspicious' ) echo '⚠ ' . esc_html__( 'Nghi ngờ', 'whp' ) . ' ' . $risk_pct . '%';
                        else echo '✗ ' . esc_html__( 'Rủi ro cao', 'whp' ) . ' ' . $risk_pct . '%';
                        ?>
                    </div>
                    <?php else : ?>
                    <div class="aipv-order-verdict pending"><?php esc_html_e( 'Chờ xác minh', 'whp' ); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php $total_pending = count( $pending_orders ); ?>
    <div class="aipv-list-footer" id="aipv-load-more-wrap"<?php echo $total_pending <= 5 ? ' style="display:none"' : ''; ?>>
        <button type="button" id="aipv-load-more" class="aipv-load-more-btn">
            <?php esc_html_e( 'Xem thêm', 'whp' ); ?> <span id="aipv-load-more-count"><?php echo max( 0, $total_pending - 5 ); ?></span> <?php esc_html_e( 'đơn', 'whp' ); ?>
        </button>
    </div>
</div>

<!-- ── COL 2: DETAIL PANEL ──────────────────────────────────── -->
<div class="aipv-detail-col">

    <div id="aipv-placeholder" class="aipv-placeholder">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
        </svg>
        <p><?php esc_html_e( 'Chọn một đơn hàng bên trái', 'whp' ); ?><br><?php esc_html_e( 'để xem chi tiết và xác minh bằng AI', 'whp' ); ?></p>
    </div>

    <div id="aipv-detail" style="display:none">

        <!-- Risk banner -->
        <div class="aipv-risk-banner" id="aipv-risk-banner">
            <span class="aipv-risk-banner-badge" id="aipv-risk-banner-badge"></span>
            <span class="aipv-risk-banner-label"><?php esc_html_e( 'Điểm rủi ro:', 'whp' ); ?> <strong id="aipv-risk-banner-score"></strong></span>
            <div class="aipv-risk-banner-bar"><div class="aipv-risk-banner-fill" id="aipv-risk-banner-fill" style="width:0%"></div></div>
        </div>

        <!-- Title -->
        <div class="aipv-detail-title">
            <h2 id="aipv-d-title">#— – —</h2>
        </div>

        <!-- Meta 4-col -->
        <div class="aipv-detail-meta">
            <div class="aipv-meta-item">
                <div class="aipv-meta-label"><?php esc_html_e( 'Thời gian', 'whp' ); ?></div>
                <div class="aipv-meta-val" id="aipv-d-confirmed">—</div>
            </div>
            <div class="aipv-meta-item">
                <div class="aipv-meta-label"><?php esc_html_e( 'Phương thức', 'whp' ); ?></div>
                <div class="aipv-meta-val" id="aipv-d-payment">—</div>
            </div>
            <div class="aipv-meta-item">
                <div class="aipv-meta-label"><?php esc_html_e( 'Số tiền', 'whp' ); ?></div>
                <div class="aipv-meta-val amount" id="aipv-d-total">—</div>
            </div>
            <div class="aipv-meta-item">
                <div class="aipv-meta-label"><?php esc_html_e( 'Người gửi', 'whp' ); ?></div>
                <div class="aipv-meta-val" id="aipv-d-sender">—</div>
            </div>
        </div>

        <!-- 2-col body -->
        <div class="aipv-detail-body">

            <!-- Receipt panel -->
            <div class="aipv-receipt-panel">
                <div class="aipv-panel-heading"><?php esc_html_e( 'Ảnh biên lai (AI Vision)', 'whp' ); ?></div>
                <div id="aipv-receipt-wrap"></div>
                <div class="aipv-receipt-actions" id="aipv-receipt-actions" style="display:none">
                    <a class="aipv-receipt-action-btn" id="aipv-btn-view-orig" href="#" target="_blank">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <?php esc_html_e( 'Xem ảnh gốc', 'whp' ); ?>
                    </a>
                    <a class="aipv-receipt-action-btn" id="aipv-btn-download" href="#" download>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <?php esc_html_e( 'Tải ảnh', 'whp' ); ?>
                    </a>
                    <button class="aipv-receipt-action-btn" id="aipv-btn-zoom">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        <?php esc_html_e( 'Phóng to', 'whp' ); ?>
                    </button>
                </div>

                <button class="aipv-btn-verify" id="aipv-verify-btn" <?php echo ! $ai_provider ? 'disabled' : ''; ?>>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    <?php esc_html_e( 'Xác minh bằng AI', 'whp' ); ?>
                </button>

                <div class="aipv-progress-wrap" id="aipv-progress-wrap">
                    <div class="aipv-progress-label">
                        <span class="aipv-progress-label-dot"></span>
                        <span id="aipv-progress-text"><?php esc_html_e( 'Đang gửi ảnh tới AI...', 'whp' ); ?></span>
                    </div>
                    <div class="aipv-progress-track"><div class="aipv-progress-fill" id="aipv-progress-fill"></div></div>
                    <div class="aipv-progress-pct" id="aipv-progress-pct">0%</div>
                    <div class="aipv-progress-steps">
                        <span class="aipv-progress-step" id="apvs-1">📤 <?php esc_html_e( 'Tải ảnh', 'whp' ); ?></span>
                        <span class="aipv-progress-step" id="apvs-2">🔍 <?php esc_html_e( 'Đọc biên lai', 'whp' ); ?></span>
                        <span class="aipv-progress-step" id="apvs-3">🧠 <?php esc_html_e( 'Phân tích AI', 'whp' ); ?></span>
                        <span class="aipv-progress-step" id="apvs-4">✅ <?php esc_html_e( 'Hoàn tất', 'whp' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- AI panel -->
            <div class="aipv-ai-panel">
                <div class="aipv-panel-heading"><?php esc_html_e( 'Kết quả phân tích AI', 'whp' ); ?></div>
                <div id="aipv-checklist">
                    <div style="color:#94a3b8;font-size:12px;text-align:center;padding:20px 0"><?php esc_html_e( 'Chưa có kết quả phân tích', 'whp' ); ?></div>
                </div>
                <div id="aipv-verdict-section" style="display:none" class="aipv-verdict-section">
                    <div class="aipv-panel-heading" style="margin-top:10px"><?php esc_html_e( 'Nhận định AI', 'whp' ); ?></div>
                    <div class="aipv-verdict-text" id="aipv-verdict-reason"></div>
                    <div id="aipv-verdict-badge-bottom"></div>
                    <div class="aipv-bank-notice">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span><strong><?php esc_html_e( 'Kiểm tra lại trong tài khoản ngân hàng.', 'whp' ); ?></strong> <?php esc_html_e( 'AI chỉ đọc ảnh biên lai, không kết nối trực tiếp với ngân hàng. Vui lòng đăng nhập tài khoản để xác nhận tiền đã vào thực tế trước khi duyệt đơn.', 'whp' ); ?></span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ── COL 3: SIDEBAR ───────────────────────────────────────── -->
<div class="aipv-sidebar-col">

    <div class="aipv-sidebar-card" id="aipv-actions-card">
        <div class="aipv-sc-title"><?php esc_html_e( 'Hành động nhanh', 'whp' ); ?></div>
        <div class="aipv-act-btns">
            <button class="aipv-act-btn confirm" id="aipv-act-confirm">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                <?php esc_html_e( 'Xác nhận thanh toán', 'whp' ); ?>
            </button>
            <button class="aipv-act-btn suspect" id="aipv-act-suspect">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <?php esc_html_e( 'Nghi ngờ / Kiểm tra thêm', 'whp' ); ?>
            </button>
            <button class="aipv-act-btn reject" id="aipv-act-reject">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                <?php esc_html_e( 'Từ chối thanh toán', 'whp' ); ?>
            </button>
            <button class="aipv-act-btn outline" id="aipv-act-receipt">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/><path d="M9 18H5a2 2 0 01-2-2V8a2 2 0 012-2h4"/></svg>
                <?php esc_html_e( 'Yêu cầu gửi lại biên lai', 'whp' ); ?>
            </button>
            <button class="aipv-act-btn outline" id="aipv-act-note">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <?php esc_html_e( 'Ghi chú nội bộ', 'whp' ); ?>
            </button>
        </div>
        <div class="aipv-note-area" id="aipv-note-area">
            <textarea class="aipv-note-input" id="aipv-note-input" placeholder="<?php esc_attr_e( 'Nhập ghi chú nội bộ...', 'whp' ); ?>"></textarea>
            <button class="aipv-note-submit" id="aipv-note-submit"><?php esc_html_e( 'Lưu ghi chú', 'whp' ); ?></button>
        </div>
    </div>

    <div class="aipv-sidebar-card aipv-bank-notice-card" id="aipv-bank-notice-card" style="display:none">
        <div class="aipv-bnc-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div class="aipv-bnc-body">
            <div class="aipv-bnc-title"><?php esc_html_e( 'Xác nhận trong tài khoản ngân hàng', 'whp' ); ?></div>
            <div class="aipv-bnc-text"><?php esc_html_e( 'AI phân tích ảnh biên lai — không kết nối ngân hàng trực tiếp. Hãy đăng nhập tài khoản để', 'whp' ); ?> <strong><?php esc_html_e( 'kiểm tra tiền đã nhận', 'whp' ); ?></strong> <?php esc_html_e( 'trước khi duyệt đơn.', 'whp' ); ?></div>
        </div>
    </div>

    <div class="aipv-sidebar-card" id="aipv-info-card">
        <div class="aipv-sc-title"><?php esc_html_e( 'Thông tin đơn hàng', 'whp' ); ?></div>
        <div class="aipv-oi-rows">
            <div class="aipv-oi-row"><span class="aipv-oi-key"><?php esc_html_e( 'Mã đơn', 'whp' ); ?></span><span class="aipv-oi-val" id="aipv-si-order">#—</span></div>
            <div class="aipv-oi-row"><span class="aipv-oi-key"><?php esc_html_e( 'Khách hàng', 'whp' ); ?></span><span class="aipv-oi-val" id="aipv-si-name">—</span></div>
            <div class="aipv-oi-row"><span class="aipv-oi-key"><?php esc_html_e( 'Email', 'whp' ); ?></span><span class="aipv-oi-val" id="aipv-si-email">—</span></div>
            <div class="aipv-oi-row"><span class="aipv-oi-key"><?php esc_html_e( 'SĐT', 'whp' ); ?></span><span class="aipv-oi-val" id="aipv-si-phone">—</span></div>
            <div class="aipv-oi-row"><span class="aipv-oi-key"><?php esc_html_e( 'Giá trị đơn', 'whp' ); ?></span><span class="aipv-oi-val" id="aipv-si-total">—</span></div>
            <div class="aipv-oi-row"><span class="aipv-oi-key"><?php esc_html_e( 'Trạng thái', 'whp' ); ?></span><span class="aipv-oi-val" id="aipv-si-status">—</span></div>
        </div>
        <a href="#" id="aipv-si-link" class="aipv-oi-detail-link" target="_blank"><?php esc_html_e( 'Xem chi tiết đơn hàng →', 'whp' ); ?></a>
    </div>

</div>

</div><!-- .aipv-layout -->
</div><!-- .aipv-wrap -->

<!-- Lightbox -->
<div class="aipv-lightbox" id="aipv-lightbox">
    <button class="aipv-lightbox-close" id="aipv-lightbox-close">×</button>
    <img id="aipv-lightbox-img" src="" alt="<?php esc_attr_e( 'Biên lai', 'whp' ); ?>">
</div>

<script>
var _aipvNonce      = '<?php echo wp_create_nonce( 'wpaap_generate_nonce' ); ?>';
var _aipvAjaxUrl    = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
var _aipvPreselect  = <?php echo absint( $_GET['order_id'] ?? 0 ); ?>;
var whpI18n = <?php echo wp_json_encode([
    'allDates'                 => __( 'Tất cả ngày', 'whp' ),
    'progressSendingToAI'      => __( 'Đang gửi ảnh tới AI...', 'whp' ),
    'progressLoadingReceipt'   => __( 'Đang tải ảnh biên lai...', 'whp' ),
    'progressAIReading'        => __( 'AI đang đọc nội dung biên lai...', 'whp' ),
    'progressAnalyzingRisk'    => __( 'Đang phân tích và kiểm tra rủi ro...', 'whp' ),
    'progressFinishing'        => __( 'Đang hoàn tất kết quả...', 'whp' ),
    'done'                     => __( 'Hoàn tất!', 'whp' ),
    'riskHighLabel'            => __( '✗ Rủi ro cao', 'whp' ),
    'riskSuspicious'           => __( '⚠ Nghi ngờ', 'whp' ),
    'riskSafe'                 => __( '✓ An toàn', 'whp' ),
    'checkAmountMatch2'        => __( 'Số tiền khớp', 'whp' ),
    'checkAccountMatch'        => __( 'Tài khoản nhận khớp', 'whp' ),
    'checkBankReceiveMatch'    => __( 'Ngân hàng/ví nhận khớp', 'whp' ),
    'checkTimeValid'           => __( 'Thời gian giao dịch hợp lệ', 'whp' ),
    'checkTransferContentMatch'=> __( 'Nội dung chuyển khoản khớp', 'whp' ),
    'checkNoTampering'         => __( 'Biên lai không có dấu hiệu chỉnh sửa', 'whp' ),
    'ocrConfidence'            => __( 'Độ tin cậy OCR', 'whp' ),
    'verdictValidShort'        => __( '✓ Hợp lệ', 'whp' ),
    'verdictSuspiciousDetail'  => __( '⚠ Nghi ngờ – Kiểm tra thêm', 'whp' ),
    'verdictInvalidDetail'     => __( '✗ Rủi ro cao – Cần kiểm tra thủ công', 'whp' ),
    'noReceiptImage'           => __( 'Chưa có ảnh biên lai', 'whp' ),
    'noAnalysisResult'         => __( 'Chưa có kết quả phân tích', 'whp' ),
    'analyzing'                => __( 'Đang phân tích...', 'whp' ),
    'reverify'                 => __( 'Xác minh lại', 'whp' ),
    'statusConfirmed'          => __( '✓ Đã xác nhận', 'whp' ),
    'statusRejected'           => __( '✗ Từ chối', 'whp' ),
    'statusSuspicious'         => __( '⚠ Nghi ngờ', 'whp' ),
    'confirmPaymentOrder'      => __( 'Xác nhận thanh toán đơn', 'whp' ),
    'rejectOrder'              => __( 'Từ chối đơn', 'whp' ),
]); ?>;
</script>
<script>
(function($){
    var $rows          = $('.aipv-order-row');
    var nonce          = _aipvNonce;
    var ajaxUrl        = _aipvAjaxUrl;
    var currentOrderId = null;
    var currentReceipt = null;

    // ── Filter bar ────────────────────────────────────────────────
    var _filterStatus = '';

    // Custom dropdown — status filter navigates server-side
    $('#aipv-filter-dd-trigger').on('click', function(e){
        e.stopPropagation();
        $(this).toggleClass('open');
        $('#aipv-filter-dd-menu').toggleClass('open');
    });
    $(document).on('click', function(){ $('#aipv-filter-dd-trigger').removeClass('open'); $('#aipv-filter-dd-menu').removeClass('open'); });
    $('.aipv-filter-dd-item').on('click', function(){
        var url = $(this).data('url');
        if (url) { window.location.href = url; return; }
        // fallback: client-side only
        var val   = $(this).data('value');
        _filterStatus = val;
        $('.aipv-filter-dd-item').removeClass('selected');
        $(this).addClass('selected');
        $('#aipv-filter-dd-trigger').removeClass('open');
        $('#aipv-filter-dd-menu').removeClass('open');
        applyFilter();
    });

    // ── Date range picker ────────────────────────────────────────────
    var _drFrom = '', _drTo = '';
    (function(){
        var trigger = document.getElementById('aipv-dr-trigger');
        var popover = document.getElementById('aipv-dr-popover');
        var label   = document.getElementById('aipv-dr-label');
        var inpFrom = document.getElementById('aipv-filter-from');
        var inpTo   = document.getElementById('aipv-filter-to');
        function fmt(d){ if(!d) return ''; var p=d.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }
        function updateLabel(){
            var f = inpFrom.value, t = inpTo.value;
            label.textContent = (f || t) ? ((f ? fmt(f) : '…') + ' – ' + (t ? fmt(t) : '…')) : whpI18n.allDates;
            trigger.classList.toggle('open', !!(f || t));
        }
        trigger.addEventListener('click', function(e){
            e.stopPropagation();
            var isOpen = popover.classList.contains('open');
            popover.classList.toggle('open', !isOpen);
            trigger.classList.toggle('open', !isOpen);
        });
        document.addEventListener('click', function(e){
            if (!trigger.closest('.aipv-dr-wrap').contains(e.target)) {
                popover.classList.remove('open');
            }
        });
        document.getElementById('aipv-dr-clear').addEventListener('click', function(){
            inpFrom.value = ''; inpTo.value = '';
            _drFrom = ''; _drTo = '';
            updateLabel();
            popover.classList.remove('open');
            trigger.classList.remove('open');
            applyFilter();
        });
        document.getElementById('aipv-dr-apply').addEventListener('click', function(){
            _drFrom = inpFrom.value;
            _drTo   = inpTo.value;
            updateLabel();
            popover.classList.remove('open');
            applyFilter();
        });
    })();

    function applyFilter() {
        var q    = $('#aipv-filter-q').val().toLowerCase().trim();
        var from = _drFrom;
        var to   = _drTo;

        $rows.each(function(){
            var $r      = $(this);
            var text    = ($r.data('order-id')+' '+$r.data('name')+' '+$r.data('total-fmt')+' '+$r.data('payment')).toLowerCase();
            var verdict = $r.find('.aipv-order-verdict').attr('class') || '';
            var conf    = $r.data('confirmed') || '';

            var matchQ = !q || text.indexOf(q) > -1;

            var matchStatus = true;
            if (_filterStatus) {
                if (_filterStatus === 'pending')    matchStatus = verdict.indexOf('pending') > -1;
                else if (_filterStatus === 'valid') matchStatus = verdict.indexOf('valid') > -1;
                else if (_filterStatus === 'suspicious') matchStatus = verdict.indexOf('suspicious') > -1;
                else if (_filterStatus === 'invalid') matchStatus = verdict.indexOf('invalid') > -1;
            }

            var matchDate = true;
            if ((from || to) && conf && conf !== '—') {
                var parts = conf.split(' ');
                var dmy   = (parts[0] || '').split('/');
                if (dmy.length >= 2) {
                    var year    = new Date().getFullYear();
                    var rowDate = new Date(year + '-' + dmy[1].padStart(2,'0') + '-' + dmy[0].padStart(2,'0'));
                    if (from) matchDate = matchDate && rowDate >= new Date(from);
                    if (to)   matchDate = matchDate && rowDate <= new Date(to);
                }
            }

            $r.toggle(matchQ && matchStatus && matchDate);
        });

        $('#aipv-filter-btn').toggleClass('active', !!(q || _filterStatus || _drFrom || _drTo));
    }

    $('#aipv-filter-q').on('input', applyFilter);
    $('#aipv-filter-btn').on('click', applyFilter);
    $('#aipv-filter-reset').on('click', function(){
        var baseUrl = $('.aipv-filter-dd-item[data-value=""]').data('url');
        if (baseUrl) { window.location.href = baseUrl; return; }
        $('#aipv-filter-q').val('');
        $('#aipv-filter-from').val('');
        $('#aipv-filter-to').val('');
        _drFrom = ''; _drTo = '';
        document.getElementById('aipv-dr-label').textContent = whpI18n.allDates;
        document.getElementById('aipv-dr-trigger').classList.remove('open');
        _filterStatus = '';
        $('.aipv-filter-dd-item').removeClass('selected');
        $('.aipv-filter-dd-item[data-value=""]').addClass('selected');
        $('#aipv-filter-dd-label').text('Tất cả');
        $('#aipv-filter-btn').removeClass('active');
        $rows.show();
    });

    // ── Progress bar ─────────────────────────────────────────────
    var _progressTimer = null, _progressVal = 0;
    var _progressSteps = [
        {id:'apvs-1', at:10,  text: whpI18n.progressLoadingReceipt},
        {id:'apvs-2', at:35,  text: whpI18n.progressAIReading},
        {id:'apvs-3', at:65,  text: whpI18n.progressAnalyzingRisk},
        {id:'apvs-4', at:95,  text: whpI18n.progressFinishing}
    ];

    function startProgress() {
        _progressVal = 0;
        clearInterval(_progressTimer);
        $('#aipv-progress-fill').css({'transition':'none','width':'0%'});
        $('#aipv-progress-pct').text('0%');
        $('#aipv-progress-text').text(whpI18n.progressSendingToAI);
        $('.aipv-progress-step').removeClass('active done');
        $('#aipv-progress-wrap').show();
        _progressTimer = setInterval(function() {
            if (_progressVal >= 92) { clearInterval(_progressTimer); return; }
            var inc = _progressVal < 30 ? 2.5 : (_progressVal < 60 ? 1.2 : 0.4);
            _progressVal = Math.min(92, _progressVal + inc);
            $('#aipv-progress-fill').css({'transition':'width .35s ease','width':_progressVal+'%'});
            $('#aipv-progress-pct').text(Math.round(_progressVal)+'%');
            _progressSteps.forEach(function(s,i) {
                var $el = $('#'+s.id);
                if (_progressVal >= s.at) {
                    var nextAt = _progressSteps[i+1] ? _progressSteps[i+1].at : 100;
                    if (_progressVal < nextAt) { $el.addClass('active').removeClass('done'); $('#aipv-progress-text').text(s.text); }
                    else { $el.addClass('done').removeClass('active'); }
                } else { $el.removeClass('active done'); }
            });
        }, 250);
    }

    function completeProgress(cb) {
        clearInterval(_progressTimer);
        $('#aipv-progress-fill').css({'transition':'width .4s ease','width':'100%'});
        $('#aipv-progress-pct').text('100%');
        $('#aipv-progress-text').text(whpI18n.done);
        $('#apvs-4').addClass('done active');
        setTimeout(function() {
            $('#aipv-progress-wrap').fadeOut(300, function() {
                $('#aipv-progress-fill').css({'transition':'none','width':'0%'});
                if (cb) cb();
            });
        }, 600);
    }

    // ── Risk banner ──────────────────────────────────────────────
    function showRiskBanner(riskRaw) {
        if (riskRaw === undefined || riskRaw === null) { $('#aipv-risk-banner').hide(); return; }
        var score = Math.round(riskRaw * 100);
        var cls   = score >= 70 ? 'high' : (score >= 30 ? 'mid' : 'low');
        var label = score >= 70 ? whpI18n.riskHighLabel : (score >= 30 ? whpI18n.riskSuspicious : whpI18n.riskSafe);
        $('#aipv-risk-banner').show().removeClass('high mid low').addClass(cls);
        $('#aipv-risk-banner-badge').text(label).removeClass('high mid low').addClass(cls);
        $('#aipv-risk-banner-score').text(score + '/100');
        $('#aipv-risk-banner-fill').css('width', score+'%').removeClass('high mid low').addClass(cls);
    }

    // ── Render result ────────────────────────────────────────────
    function renderResult(res) {
        showRiskBanner(res.risk_score);

        function checkItem(ok, label, val) {
            var cls = ok ? 'pass' : 'fail';
            return '<div class="aipv-check-item">'
                 + '<span class="aipv-check-icon '+cls+'">'+(ok?'✓':'✗')+'</span>'
                 + '<span class="aipv-check-label">'+label+'</span>'
                 + (val ? '<span class="aipv-check-val '+cls+'">'+val+'</span>' : '')
                 + '</div>';
        }

        var cl = '';
        if (typeof res.amount_match !== 'undefined') {
            var amtStr = res.amount ? parseInt(res.amount).toLocaleString('vi-VN')+' VND' : '';
            cl += checkItem(res.amount_match, whpI18n.checkAmountMatch2, amtStr);
        }
        if (typeof res.account_match !== 'undefined') {
            cl += checkItem(res.account_match, whpI18n.checkAccountMatch, res.account_to || '');
        }
        if (typeof res.bank_match !== 'undefined') {
            cl += checkItem(res.bank_match, whpI18n.checkBankReceiveMatch, res.bank || '');
        }
        if (typeof res.time_match !== 'undefined') {
            cl += checkItem(res.time_match, whpI18n.checkTimeValid, '');
        }
        if (typeof res.note_match !== 'undefined') {
            cl += checkItem(res.note_match, whpI18n.checkTransferContentMatch, res.note || '');
        }
        if (typeof res.edited !== 'undefined') {
            cl += checkItem(!res.edited, whpI18n.checkNoTampering, '');
        }
        var conf = typeof res.confidence !== 'undefined' ? parseInt(res.confidence) : Math.round((1-(res.risk_score||0))*100);
        cl += '<div class="aipv-conf-item">'
            + '<span class="aipv-conf-label">' + whpI18n.ocrConfidence + '</span>'
            + '<div class="aipv-conf-bar-wrap"><div class="aipv-conf-bar-fill" style="width:'+conf+'%"></div></div>'
            + '<span class="aipv-conf-pct">'+conf+'%</span></div>';
        $('#aipv-checklist').html(cl);

        if (res.verdict_reason || res.verdict) {
            var badgeMap = {
                valid:      whpI18n.verdictValidShort,
                suspicious: whpI18n.verdictSuspiciousDetail,
                invalid:    whpI18n.verdictInvalidDetail
            };
            $('#aipv-verdict-reason').text(res.verdict_reason || '');
            $('#aipv-verdict-badge-bottom').html('<span class="aipv-verdict-badge-bottom '+(res.verdict||'')+'">'+(badgeMap[res.verdict]||res.verdict)+'</span>');
            $('#aipv-verdict-section').show();
        }
    }

    // ── Select order ─────────────────────────────────────────────
    function selectOrder($row) {
        $rows.removeClass('active');
        $row.addClass('active');
        currentOrderId = $row.data('order-id');
        currentReceipt = $row.data('receipt') || null;

        $('#aipv-placeholder').hide();
        $('#aipv-detail').show();
        $('#aipv-risk-banner').hide();
        $('#aipv-verdict-section').hide();

        var name = $row.data('name') || 'Khách';
        $('#aipv-d-title').text('#' + currentOrderId + ' – ' + name);
        $('#aipv-d-confirmed').text($row.data('confirmed') || '—');
        $('#aipv-d-payment').text($row.data('payment') || $row.data('bank') || '—');
        $('#aipv-d-total').text($row.data('total-fmt') || '—');
        var sender  = $row.data('sender') || '';
        var last4   = $row.data('last4')  || '';
        var phone   = $row.data('phone')  || '';
        $('#aipv-d-sender').text([sender, last4 ? '····'+last4 : ''].filter(Boolean).join(' – ') || phone || '—');

        // Sidebar info
        $('#aipv-si-order').text('#' + currentOrderId);
        $('#aipv-si-name').text(name);
        $('#aipv-si-email').text($row.data('email') || '—');
        $('#aipv-si-phone').text(phone || '—');
        $('#aipv-si-total').text($row.data('total-fmt') || '—');
        var slug = $row.data('status-slug') || '';
        $('#aipv-si-status').text($row.data('status') || '—').removeClass('pending processing cancelled')
            .addClass(slug === 'on-hold' ? 'pending' : (slug === 'processing' ? 'processing' : (slug === 'cancelled' ? 'cancelled' : '')));
        $('#aipv-si-link').attr('href', $row.data('order-url') || '#');
        $('#aipv-actions-card, #aipv-info-card, #aipv-bank-notice-card').show();

        // Receipt
        if (currentReceipt) {
            $('#aipv-receipt-wrap').html('<img src="'+currentReceipt+'" class="aipv-receipt-img" id="aipv-receipt-img" alt="Biên lai">');
            $('#aipv-receipt-actions').show();
            $('#aipv-btn-view-orig').attr('href', currentReceipt);
            $('#aipv-btn-download').attr('href', currentReceipt);
        } else {
            $('#aipv-receipt-wrap').html('<div class="aipv-receipt-placeholder"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>' + whpI18n.noReceiptImage + '</div>');
            $('#aipv-receipt-actions').hide();
        }

        $('#aipv-checklist').html('<div style="color:#94a3b8;font-size:12px;text-align:center;padding:20px 0">' + whpI18n.noAnalysisResult + '</div>');

        var existing = $row.data('ai-result');
        if (existing && typeof existing === 'object') { renderResult(existing); }
        else if (existing) { try { renderResult(JSON.parse(existing)); } catch(e) {} }
    }

    $(document).on('click', '.aipv-order-row', function(){ selectOrder($(this)); });

    // ── Lightbox ─────────────────────────────────────────────────
    $(document).on('click', '#aipv-receipt-img, #aipv-btn-zoom', function(e){
        e.preventDefault();
        if (!currentReceipt) return;
        $('#aipv-lightbox-img').attr('src', currentReceipt);
        $('#aipv-lightbox').addClass('open');
    });
    $('#aipv-lightbox, #aipv-lightbox-close').on('click', function(e){
        if (e.target === this) $('#aipv-lightbox').removeClass('open');
    });
    $(document).on('keyup', function(e){ if (e.key === 'Escape') $('#aipv-lightbox').removeClass('open'); });

    // ── Verify button ─────────────────────────────────────────────
    var SVG_SHIELD = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>';

    $('#aipv-verify-btn').on('click', function(){
        if (!currentOrderId) return;
        var $btn = $(this);
        $btn.addClass('loading').prop('disabled', true)
            .html('<svg class="aipv-spinner" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".25"/><path d="M21 12a9 9 0 00-9-9"/></svg> ' + whpI18n.analyzing);
        startProgress();

        $.post(ajaxUrl, {action:'wpaap_aipay_verify', nonce:nonce, order_id:currentOrderId}, function(resp){
            completeProgress(function(){
                $btn.removeClass('loading').prop('disabled', false).html(SVG_SHIELD+' ' + whpI18n.reverify);
                if (resp.success) {
                    renderResult(resp.data);
                    var verdict = resp.data.verdict;
                    var risk    = Math.round((resp.data.risk_score||0)*100);
                    var vLabel  = verdict==='valid'      ? '✓ Đã xác minh'+(risk?' '+risk+'%':'')
                                : verdict==='suspicious' ? '⚠ Nghi ngờ '+risk+'%'
                                : '✗ Rủi ro cao '+risk+'%';
                    $rows.filter('[data-order-id="'+currentOrderId+'"]').find('.aipv-order-verdict')
                        .removeClass('pending valid suspicious invalid').addClass(verdict).text(vLabel);
                } else {
                    wpaapToast('Lỗi: '+(resp.data&&resp.data.message?resp.data.message:'Thử lại sau.'), 'error');
                }
            });
        }).fail(function(){
            clearInterval(_progressTimer);
            $('#aipv-progress-wrap').hide();
            $btn.removeClass('loading').prop('disabled', false).html(SVG_SHIELD+' Xác minh bằng AI');
            wpaapToast('Lỗi kết nối. Thử lại sau.', 'error');
        });
    });

    // ── Action buttons ────────────────────────────────────────────
    function doAction(type, note, $btn, successMsg) {
        if (!currentOrderId) return;
        var orig = $btn.html();
        $btn.prop('disabled', true).html('<svg class="aipv-spinner" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" opacity=".25"/><path d="M21 12a9 9 0 00-9-9"/></svg>');
        $.post(ajaxUrl, {action:'wpaap_aipay_order_action', nonce:nonce, order_id:currentOrderId, type:type, note:note||''}, function(resp){
            $btn.prop('disabled', false).html(orig);
            if (resp.success) {
                if (successMsg) wpaapToast(successMsg, 'success');
                var $row = $rows.filter('[data-order-id="'+currentOrderId+'"]');
                if (type==='confirm') $row.find('.aipv-order-verdict').removeClass('pending invalid suspicious').addClass('valid').text(whpI18n.statusConfirmed);
                if (type==='reject')  $row.find('.aipv-order-verdict').removeClass('pending valid suspicious').addClass('invalid').text(whpI18n.statusRejected);
                if (type==='suspect') $row.find('.aipv-order-verdict').removeClass('pending valid invalid').addClass('suspicious').text(whpI18n.statusSuspicious);
            } else {
                wpaapToast('Lỗi: '+(resp.data&&resp.data.message?resp.data.message:'Thử lại sau.'), 'error');
            }
        }).fail(function(){
            $btn.prop('disabled', false).html(orig);
            wpaapToast('Lỗi kết nối. Thử lại sau.', 'error');
        });
    }

    $('#aipv-act-confirm').on('click', function(){
        if (!currentOrderId) return;
        var $self = $(this);
        wpaapConfirm(whpI18n.confirmPaymentOrder + ' #'+currentOrderId+'?', function() {
            doAction('confirm','', $self,'Đã xác nhận thanh toán!');
        }, { title: 'Xác nhận thanh toán', okLabel: 'Xác nhận' });
        return;
    });
    $('#aipv-act-suspect').on('click', function(){
        if (!currentOrderId) return;
        doAction('suspect','',$(this),'Đã đánh dấu nghi ngờ.');
    });
    $('#aipv-act-reject').on('click', function(){
        if (!currentOrderId) return;
        var $self = $(this);
        wpaapConfirm(whpI18n.rejectOrder + ' #'+currentOrderId+'?', function() {
            doAction('reject','', $self,'Đã từ chối thanh toán.');
        }, { title: 'Từ chối thanh toán', okLabel: 'Từ chối', danger: true });
        return;
    });
    $('#aipv-act-receipt').on('click', function(){
        if (!currentOrderId) return;
        doAction('request_receipt','',$(this),'Đã gửi yêu cầu biên lai tới khách!');
    });
    $('#aipv-act-note').on('click', function(){
        $('#aipv-note-area').toggle();
        if ($('#aipv-note-area').is(':visible')) $('#aipv-note-input').focus();
    });
    $('#aipv-note-submit').on('click', function(){
        var note = $('#aipv-note-input').val().trim();
        if (!note || !currentOrderId) return;
        doAction('note', note, $(this), '');
        setTimeout(function(){ $('#aipv-note-area').hide(); $('#aipv-note-input').val(''); }, 800);
    });

    // Auto-select: ưu tiên order_id từ URL, fallback row đầu tiên
    if (_aipvPreselect) {
        var $target = $rows.filter('[data-order-id="' + _aipvPreselect + '"]');
        if ($target.length) {
            $target[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
            $target.trigger('click');
        } else {
            $('.aipv-order-row:not(.aipv-row-hidden)').first().trigger('click');
        }
    } else {
        $('.aipv-order-row:not(.aipv-row-hidden)').first().trigger('click');
    }

    // Load more pending orders
    var aipvShown = 5;
    $('#aipv-load-more').on('click', function() {
        var $hidden = $('.aipv-order-row.aipv-row-hidden');
        $hidden.slice(0, 5).removeClass('aipv-row-hidden');
        aipvShown += 5;
        var remaining = $('.aipv-order-row.aipv-row-hidden').length;
        if (remaining === 0) {
            $('#aipv-load-more-wrap').hide();
        } else {
            $('#aipv-load-more-count').text(remaining);
        }
    });

})(jQuery);
</script>
<?php
}
