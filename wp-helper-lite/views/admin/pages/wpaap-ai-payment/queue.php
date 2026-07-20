<?php defined('ABSPATH') || exit;

function wpaap_aipay_queue_layout() {
    // ─── Date filter from GET ──────────────────────────────────────
    $f_from_raw = sanitize_text_field( $_GET['aipq_from'] ?? '' );
    $f_to_raw   = sanitize_text_field( $_GET['aipq_to']   ?? '' );
    $f_ts_from  = $f_from_raw ? (int) strtotime( $f_from_raw . ' 00:00:00' ) : 0;
    $f_ts_to    = $f_to_raw   ? (int) strtotime( $f_to_raw   . ' 23:59:59' ) : 0;

    // ─── Data ─────────────────────────────────────────────────────
    $all_rows = [];
    if (function_exists('wc_get_orders')) {
        $raw = wc_get_orders([
            'status'     => ['on-hold'],
            'meta_query' => [['key' => '_whp_transfer_confirmed_at', 'compare' => 'EXISTS']],
            'limit'      => 500,
            'orderby'    => 'date',
            'order'      => 'DESC',
            'return'     => 'objects',
        ]);
        foreach ($raw as $order) {
            $ai       = $order->get_meta('_whp_ai_verify_result');
            $risk_pct = null;
            if (is_array($ai) && isset($ai['risk_score'])) {
                $risk_pct = (int) round(floatval($ai['risk_score']) * 100);
            }
            if ($risk_pct === null) {
                $total    = floatval($order->get_total());
                $risk_pct = 28;
                if ($total >= 5000000)      $risk_pct = 80;
                elseif ($total >= 2000000)  $risk_pct = 58;
                elseif ($total >= 1000000)  $risk_pct = 42;
                if (empty($order->get_meta('_whp_transfer_receipt'))) {
                    $risk_pct = min(95, $risk_pct + 18);
                }
            }
            $method    = $order->get_payment_method();
            $is_wallet = in_array($method, ['MB_WHP_Wallet_MoMo','MB_WHP_Wallet_ZaloPay','MB_WHP_Wallet_VNPAY','MB_WHP_Wallet_ShopeePay'], true);
            $conf_at   = $order->get_meta('_whp_transfer_confirmed_at');
            $conf_ts   = $conf_at ? (int) strtotime($conf_at) : (int) $order->get_date_modified()->getTimestamp();
            $is_late   = (time() - $conf_ts) > 86400;
            $risk_lvl  = $risk_pct >= 65 ? 'high' : ($risk_pct >= 35 ? 'medium' : 'low');
            $all_rows[] = compact('order','risk_pct','risk_lvl','is_late','is_wallet','conf_ts','conf_at');
        }
    }

    // ─── Apply date filter ─────────────────────────────────────────
    if ( $f_ts_from || $f_ts_to ) {
        $all_rows = array_values( array_filter( $all_rows, function( $r ) use ( $f_ts_from, $f_ts_to ) {
            if ( $f_ts_from && $r['conf_ts'] < $f_ts_from ) return false;
            if ( $f_ts_to   && $r['conf_ts'] > $f_ts_to   ) return false;
            return true;
        } ) );
    }

    $n        = count($all_rows);
    $n_high   = count(array_filter($all_rows, fn($r) => $r['risk_lvl'] === 'high'));
    $n_med    = count(array_filter($all_rows, fn($r) => $r['risk_lvl'] === 'medium'));
    $n_low    = count(array_filter($all_rows, fn($r) => $r['risk_lvl'] === 'low'));
    $n_late   = count(array_filter($all_rows, fn($r) => $r['is_late']));
    $n_bank   = count(array_filter($all_rows, fn($r) => !$r['is_wallet']));
    $n_wallet = count(array_filter($all_rows, fn($r) => $r['is_wallet']));

    // Donut degrees
    $dh = $n ? round($n_high / $n * 360) : 120;
    $dm = $n ? round($n_med  / $n * 360) : 120;
    $ph = $n ? round($n_high / $n * 100) : 0;
    $pm = $n ? round($n_med  / $n * 100) : 0;
    $pl = max(0, 100 - $ph - $pm);

    $pb = $n ? round($n_bank   / $n * 100) : 0;
    $pw = $n ? round($n_wallet / $n * 100) : 0;

    // Sparkline — hourly counts today
    $today0 = strtotime('today');
    $slots  = array_fill(0, 24, 0);
    foreach ($all_rows as $r) {
        if ($r['conf_ts'] >= $today0) {
            $h = min(23, (int)(($r['conf_ts'] - $today0) / 3600));
            $slots[$h]++;
        }
    }
    $mx  = max(1, max($slots));
    $SVW = 200; $SVH = 40;
    $line_pts = []; $area_pts = [];
    foreach ($slots as $i => $v) {
        $x = round($i / 23 * $SVW, 1);
        $y = round($SVH - ($v / $mx * $SVH * 0.85) - 1, 1);
        $line_pts[] = $x . ',' . $y;
    }
    $area_pts = $line_pts[0] . ' ' . implode(' ', $line_pts) . ' ' . $SVW . ',' . $SVH . ' 0,' . $SVH;
    $spark    = implode(' ', $line_pts);
    $peak_val = max($slots);
    $peak_h   = array_search($peak_val, $slots);
    $pk_x     = round($peak_h / 23 * $SVW);
    $pk_y     = $peak_val > 0 ? round($SVH - ($peak_val / $mx * $SVH * 0.85) - 1) : $SVH / 2;

    $verify_url  = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=verify');
    $config_url  = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=config');
    $notif_url   = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=notifications');
    $date_from   = $f_from_raw ? date_i18n( 'd/m/Y', $f_ts_from ) : date_i18n( 'd/m/Y', strtotime( '-7 days' ) );
    $date_to     = $f_to_raw   ? date_i18n( 'd/m/Y', $f_ts_to   ) : date_i18n( 'd/m/Y' );
    $ajax_nonce  = wp_create_nonce('wpaap_generate_nonce');
    ?>
<style>
/* ═══ QUEUE REDESIGN ════════════════════════════════════════════ */
.aipq-outer{display:flex;flex-direction:column;gap:14px;}
/* Filter bar */
.aipq-bar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.aipq-tabs{display:flex;align-items:center;gap:5px;flex:1;flex-wrap:wrap;}
.aipq-tab{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:20px;border:1px solid #e2e8f0;background:#fff;font-size:12.5px;font-weight:500;color:#475569;cursor:pointer;transition:all .14s;white-space:nowrap;outline:none;}
.aipq-tab .aipq-cnt{font-size:11px;font-weight:700;padding:1px 6px;border-radius:10px;background:#f1f5f9;color:#64748b;transition:all .14s;}
.aipq-tab:hover{border-color:#cbd5e1;background:#f8fafc;}
.aipq-tab.active{background:#fff1f2;border-color:#fecdd3;color:#e11d48;}
.aipq-tab.active .aipq-cnt{background:#fee2e2;color:#e11d48;}
.aipq-tab.tab-high.active{background:#fff1f2;border-color:#fecdd3;color:#dc2626;}
.aipq-tab.tab-high.active .aipq-cnt{background:#fee2e2;color:#dc2626;}
.aipq-tab.tab-med.active{background:#fff7ed;border-color:#fed7aa;color:#c2410c;}
.aipq-tab.tab-med.active .aipq-cnt{background:#ffedd5;color:#c2410c;}
.aipq-tab.tab-low.active{background:#f0fdf4;border-color:#bbf7d0;color:#16a34a;}
.aipq-tab.tab-low.active .aipq-cnt{background:#dcfce7;color:#16a34a;}
.aipq-tab.tab-late.active{background:#fefce8;border-color:#fde68a;color:#b45309;}
.aipq-tab.tab-late.active .aipq-cnt{background:#fef9c3;color:#b45309;}
/* Toolbar */
.aipq-toolbar{display:flex;align-items:center;gap:7px;flex-shrink:0;}
.aipq-daterange{display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;color:#374151;cursor:pointer;white-space:nowrap;transition:border-color .15s,color .15s;}
.aipq-daterange:hover,.aipq-daterange.open{border-color:#e11d48;color:#e11d48;}
.aipq-daterange-wrap{position:relative;}
.aipq-date-popover{display:none;position:absolute;top:calc(100% + 7px);right:0;z-index:9999;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.13);padding:14px 16px;min-width:230px;}
.aipq-date-popover.open{display:block;}
.aipq-date-row{display:flex;flex-direction:column;gap:4px;margin-bottom:10px;}
.aipq-date-row label{font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;}
.aipq-date-row input[type="date"]{width:100%;height:32px;border:1px solid #e2e8f0!important;border-radius:7px!important;padding:0 8px!important;font-size:12px!important;color:#374151!important;background:#f8fafc!important;box-shadow:none!important;cursor:pointer;}
.aipq-date-row input[type="date"]:focus{border-color:#e11d48!important;outline:none!important;}
.aipq-date-actions{display:flex;gap:7px;margin-top:6px;}
.aipq-date-apply{flex:1;padding:7px 12px;border:none;border-radius:7px;background:#e11d48;color:#fff;font-size:12px;font-weight:600;cursor:pointer;}
.aipq-date-apply:hover{background:#be123c;}
.aipq-date-clear{padding:7px 10px;border:1px solid #e2e8f0;border-radius:7px;background:#fff;color:#64748b;font-size:12px;cursor:pointer;}
.aipq-date-clear:hover{background:#f8fafc;}
.aipq-tool-btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;font-weight:500;color:#374151;cursor:pointer;transition:all .14s;}
.aipq-tool-btn:hover{border-color:#cbd5e1;background:#f8fafc;}
.aipq-tool-btn.icon-only{padding:6px 8px;}
/* Grid — sidebar column = (W - 3×14px) / 4 = matches stats 4th column */
.aipq-grid{display:grid;grid-template-columns:1fr minmax(240px,calc(25% - 10.5px));gap:14px;align-items:start;min-width:0;}
@media(max-width:960px){.aipq-grid{grid-template-columns:1fr;}}
/* Table card */
.aipq-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);}
.aipq-table{width:100%;border-collapse:collapse;font-size:12.5px;}
.aipq-table th{padding:9px 10px;background:#fafafa;border-bottom:1px solid #e2e8f0;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap;text-align:left;}
.aipq-table th:first-child,.aipq-table td:first-child{width:34px;padding-left:14px;}
.aipq-table th:last-child,.aipq-table td:last-child{padding-right:14px;}
.aipq-table td{padding:10px 10px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.aipq-table tbody tr:last-child td{border-bottom:none;}
.aipq-table tbody tr:hover td{background:#fafafa;}
.aipq-oid{font-size:12px;font-weight:700;color:#059669;}
.aipq-odate{font-size:10.5px;color:#94a3b8;margin-top:2px;}
.aipq-name{font-size:12.5px;font-weight:600;color:#0f172a;}
.aipq-phone{font-size:11px;color:#94a3b8;margin-top:1px;}
.aipq-amt{font-size:13px;font-weight:700;color:#0f172a;}
.aipq-bank{font-size:12px;color:#475569;}
.aipq-time{font-size:11.5px;color:#64748b;}
/* Risk */
.aipq-risk{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;}
.aipq-rdot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.aipq-rdot-high{background:#ef4444;box-shadow:0 0 0 2.5px rgba(239,68,68,.18);}
.aipq-rdot-med{background:#f97316;box-shadow:0 0 0 2.5px rgba(249,115,22,.18);}
.aipq-rdot-low{background:#22c55e;box-shadow:0 0 0 2.5px rgba(34,197,94,.18);}
.risk-high-c{color:#dc2626;}
.risk-med-c{color:#ea580c;}
.risk-low-c{color:#16a34a;}
/* Status badge */
.aipq-badge{display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600;background:#fff7ed;color:#c2410c;border:1px solid #fed7aa;}
/* Actions */
.aipq-actions{display:flex;align-items:center;gap:4px;}
.aipq-action-btn{display:inline-flex;align-items:center;justify-content:center;width:27px;height:27px;border-radius:7px;border:1px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;transition:all .14s;text-decoration:none;}
.aipq-action-btn:hover{border-color:#cbd5e1;background:#f8fafc;color:#0f172a;}
/* Pagination */
.aipq-pag{display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-top:1px solid #f1f5f9;font-size:12px;color:#64748b;gap:8px;}
.aipq-pag-btns{display:flex;align-items:center;gap:3px;}
.aipq-pag-btn{display:inline-flex;align-items:center;justify-content:center;min-width:27px;height:27px;padding:0 4px;border-radius:7px;border:1px solid #e2e8f0;background:#fff;font-size:12px;color:#374151;cursor:pointer;transition:all .14s;}
.aipq-pag-btn:hover{border-color:#cbd5e1;background:#f8fafc;}
.aipq-pag-btn.active{background:#e11d48;border-color:#e11d48;color:#fff;font-weight:700;}
.aipq-pag-btn:disabled{opacity:.4;cursor:not-allowed;}
.aipq-pag-size{display:inline-flex;align-items:center;gap:5px;white-space:nowrap;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:3px 10px 3px 10px;transition:border-color .15s;}
.aipq-pag-size:focus-within{border-color:#cbd5e1;}
.aipq-pag-size-lbl,.aipq-pag-size-unit{font-size:11.5px;color:#94a3b8;font-weight:500;}
.aipq-pag-sel,
.wp-core-ui select.aipq-pag-sel{
    border:none!important;background:transparent!important;box-shadow:none!important;
    padding:2px 2px!important;font-size:12px!important;font-weight:700!important;
    color:#374151!important;border-radius:0!important;min-width:36px;cursor:pointer;
}
/* Empty */
.aipq-empty{text-align:center;padding:48px 20px;}
.aipq-no-result{display:none;text-align:center;padding:28px;color:#94a3b8;font-size:13px;}
/* Check */
.aipq-chk{width:14px;height:14px;cursor:pointer;accent-color:#e11d48;}
/* ── Sidebar cards ── */
.aipq-scard{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:15px;box-shadow:0 1px 3px rgba(0,0,0,.04);margin-bottom:11px;}
.aipq-scard:last-child{margin-bottom:0;}
.aipq-sc-title{font-size:13px;font-weight:700;color:#0f172a;margin:0 0 13px;}
/* Donut */
.aipq-donut-wrap{display:flex;align-items:center;gap:14px;}
.aipq-donut{position:relative;width:88px;height:88px;border-radius:50%;flex-shrink:0;}
.aipq-donut-hole{position:absolute;width:58px;height:58px;border-radius:50%;background:#fff;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;align-items:center;justify-content:center;}
.aipq-donut-tot{font-size:18px;font-weight:800;color:#0f172a;}
.aipq-legend{display:flex;flex-direction:column;gap:7px;}
.aipq-leg-item{display:flex;align-items:center;gap:7px;font-size:11.5px;color:#475569;}
.aipq-leg-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
.aipq-leg-val{font-weight:700;color:#0f172a;margin-left:auto;padding-left:6px;white-space:nowrap;}
/* Bulk btn */
.aipq-bulk-btn{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;margin-top:13px;padding:9px 12px;background:#fff1f2;border:1.5px solid #fecdd3;border-radius:10px;font-size:12.5px;font-weight:700;color:#e11d48;cursor:pointer;transition:all .14s;text-decoration:none;}
.aipq-bulk-btn:hover{background:#fee2e2;border-color:#fca5a5;color:#be123c;}
.aipq-bulk-btn:disabled{opacity:.6;cursor:not-allowed;}
/* Sparkline */
.aipq-spark-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.aipq-spark-sel{font-size:11.5px;color:#64748b;border:1px solid #e2e8f0;border-radius:7px;padding:3px 7px;background:#fff;cursor:pointer;}
.aipq-sparkline{width:100%;height:48px;overflow:visible;}
.aipq-spark-foot{display:flex;justify-content:space-between;font-size:10px;color:#cbd5e1;margin-top:3px;}
/* Channels */
.aipq-chan-row{display:flex;align-items:center;gap:7px;margin-bottom:9px;font-size:12px;color:#475569;}
.aipq-chan-row:last-child{margin-bottom:0;}
.aipq-chan-lbl{flex:1;min-width:0;}
.aipq-chan-bar{width:72px;height:5px;background:#f1f5f9;border-radius:5px;flex-shrink:0;}
.aipq-chan-fill{height:100%;border-radius:5px;}
.aipq-chan-cnt{font-weight:700;color:#0f172a;min-width:16px;text-align:right;}
.aipq-chan-pct{color:#94a3b8;min-width:32px;text-align:right;}
/* Suggestions */
.aipq-sug-item{display:flex;align-items:center;gap:9px;padding:9px 0;border-bottom:1px solid #f1f5f9;text-decoration:none;transition:all .12s;}
.aipq-sug-item:last-child{border-bottom:none;padding-bottom:0;}
.aipq-sug-item:hover .aipq-sug-title{color:#e11d48;}
.aipq-sug-ico{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.aipq-sug-ico.blue{background:#eff6ff;}
.aipq-sug-ico.amber{background:#fffbeb;}
.aipq-sug-body{flex:1;min-width:0;}
.aipq-sug-title{font-size:12px;font-weight:600;color:#0f172a;line-height:1.35;}
.aipq-sug-desc{font-size:11px;color:#94a3b8;margin-top:2px;}
.aipq-sug-arr{color:#cbd5e1;flex-shrink:0;}
/* Dropdown */
.aipq-dd-menu{display:none;position:fixed;z-index:99999;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 4px 18px rgba(0,0,0,.1);padding:4px;min-width:156px;}
.aipq-dd-item{display:flex;align-items:center;gap:7px;padding:7px 10px;border-radius:7px;font-size:12.5px;color:#374151;cursor:pointer;transition:background .12s;text-decoration:none;border:none;background:none;width:100%;}
.aipq-dd-item:hover{background:#f8fafc;}
.aipq-dd-sep{height:1px;background:#f1f5f9;margin:3px 0;}
@keyframes spin{to{transform:rotate(360deg);}}
/* ─── Order detail popup (shared with risk tab) ─── */
.rskp-overlay{display:none;position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:999990;align-items:center;justify-content:center;padding:16px;}
.rskp-overlay.open{display:flex;}
.rskp-modal{background:#fff;border-radius:16px;width:100%;max-width:960px;max-height:90vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.2);}
.rskp-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;border-bottom:1px solid #f1f5f9;flex-shrink:0;}
.rskp-head-left{display:flex;align-items:center;gap:10px;}
.rskp-title{font-size:16px;font-weight:700;color:#0f172a;}
.rskp-risk-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.rskp-risk-badge.high{background:#fee2e2;color:#991b1b;}
.rskp-risk-badge.medium{background:#fef3c7;color:#92400e;}
.rskp-risk-badge.low{background:#d1fae5;color:#065f46;}
.rskp-close{width:30px;height:30px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:18px;line-height:1;transition:all .13s;}
.rskp-close:hover{background:#f8fafc;color:#0f172a;}
.rskp-meta{display:grid;grid-template-columns:repeat(4,1fr);border-bottom:1px solid #f1f5f9;flex-shrink:0;}
.rskp-meta-item{padding:10px 16px;border-right:1px solid #f1f5f9;}
.rskp-meta-item:last-child{border-right:none;}
.rskp-meta-lbl{font-size:10.5px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px;}
.rskp-meta-val{font-size:12.5px;font-weight:600;color:#0f172a;}
.rskp-meta-val.amount{color:#059669;}
.rskp-body{display:grid;grid-template-columns:1fr 220px;overflow:hidden;flex:1;min-height:0;}
.rskp-main{display:grid;grid-template-columns:1fr 1fr;border-right:1px solid #f1f5f9;overflow-y:auto;}
.rskp-receipt-panel{padding:16px;border-right:1px solid #f1f5f9;}
.rskp-ai-panel{padding:16px;overflow-y:auto;}
.rskp-panel-hd{font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;padding-bottom:7px;border-bottom:1px solid #f1f5f9;}
.rskp-receipt-img{width:100%;max-height:240px;object-fit:contain;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc;display:block;cursor:zoom-in;}
.rskp-receipt-placeholder{min-height:120px;background:#f8fafc;border-radius:10px;border:2px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;gap:7px;text-align:center;}
.rskp-receipt-actions{display:flex;gap:6px;margin-top:8px;}
.rskp-receipt-btn{flex:1;padding:6px 4px;border:1px solid #e2e8f0;border-radius:7px;background:#fff;font-size:11px;color:#475569;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:4px;transition:background .1s;}
.rskp-receipt-btn:hover{background:#f1f5f9;color:#0f172a;}
.rskp-btn-verify{width:100%;margin-top:10px;padding:10px;border:none;border-radius:9px;cursor:pointer;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:7px;background:linear-gradient(135deg,#e11d48,#be123c);color:#fff;box-shadow:0 3px 12px rgba(225,29,72,.2);transition:opacity .15s;}
.rskp-btn-verify:hover{opacity:.9;}
.rskp-btn-verify:disabled{opacity:.5;cursor:not-allowed;}
.rskp-checklist{display:flex;flex-direction:column;gap:8px;}
.rskp-check-item{display:flex;align-items:center;gap:8px;font-size:12.5px;}
.rskp-check-icon{width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;}
.rskp-check-icon.pass{background:#d1fae5;color:#059669;}
.rskp-check-icon.fail{background:#fee2e2;color:#dc2626;}
.rskp-check-label{flex:1;color:#374151;}
.rskp-check-val{font-size:11.5px;font-weight:600;max-width:110px;text-align:right;word-break:break-all;}
.rskp-check-val.pass{color:#059669;}
.rskp-check-val.fail{color:#dc2626;}
.rskp-verdict-section{margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9;}
.rskp-verdict-text{font-size:12px;color:#374151;line-height:1.55;margin-bottom:9px;}
.rskp-verdict-badge{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700;}
.rskp-verdict-badge.valid{background:#d1fae5;color:#065f46;}
.rskp-verdict-badge.suspicious{background:#fef3c7;color:#92400e;}
.rskp-verdict-badge.invalid{background:#fee2e2;color:#991b1b;}
.rskp-sidebar{overflow-y:auto;display:flex;flex-direction:column;gap:0;}
.rskp-sc{border-bottom:1px solid #f1f5f9;}
.rskp-sc-title{padding:10px 14px;font-size:12px;font-weight:700;color:#0f172a;background:#fafafa;border-bottom:1px solid #f1f5f9;}
.rskp-act-btns{padding:10px;display:flex;flex-direction:column;gap:7px;}
.rskp-act-btn{width:100%;padding:8px 11px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;text-align:left;display:flex;align-items:center;gap:6px;transition:opacity .15s;border:none;}
.rskp-act-btn:hover{opacity:.85;}
.rskp-act-btn.confirm{background:#22c55e;color:#fff;}
.rskp-act-btn.suspect{background:#f59e0b;color:#fff;}
.rskp-act-btn.reject{background:#fef2f2;color:#dc2626;border:1px solid #fecaca;}
.rskp-act-btn.outline{background:#fff;color:#475569;border:1px solid #e2e8f0;}
.rskp-note-wrap{padding:0 10px 10px;display:none;}
.rskp-note-input{width:100%;min-height:56px;resize:vertical;border:1px solid #d1d5db;border-radius:8px;padding:7px 9px;font-size:12px;color:#0f172a;box-sizing:border-box;font-family:inherit;}
.rskp-note-submit{margin-top:5px;padding:5px 12px;border:none;border-radius:7px;background:#0f172a;color:#fff;font-size:11.5px;font-weight:600;cursor:pointer;}
.rskp-oi-rows{padding:8px 14px;display:flex;flex-direction:column;gap:7px;}
.rskp-oi-row{display:flex;justify-content:space-between;align-items:flex-start;gap:5px;font-size:11.5px;}
.rskp-oi-key{color:#94a3b8;font-weight:600;flex-shrink:0;}
.rskp-oi-val{color:#0f172a;font-weight:600;text-align:right;word-break:break-all;}
.rskp-oi-val.on-hold,.rskp-oi-val.pending{color:#d97706;}
.rskp-oi-val.processing{color:#059669;}
.rskp-oi-val.cancelled{color:#dc2626;}
.rskp-oi-link{display:block;text-align:center;padding:7px 14px 10px;font-size:12px;font-weight:600;color:#e11d48;text-decoration:none;}
.rskp-oi-link:hover{text-decoration:underline;}
@keyframes rskp-spin{to{transform:rotate(360deg);}}
.rskp-spinner{animation:rskp-spin .8s linear infinite;}
.rskp-lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999999;align-items:center;justify-content:center;}
.rskp-lightbox.open{display:flex;}
.rskp-lightbox img{max-width:90vw;max-height:90vh;object-fit:contain;border-radius:10px;}
.rskp-lightbox-close{position:absolute;top:16px;right:20px;color:#fff;font-size:32px;cursor:pointer;background:none;border:none;line-height:1;}
</style>

<div class="aipq-outer">

    <!-- ── Filter bar ──────────────────────────────────────────── -->
    <div class="aipq-bar">
        <div class="aipq-tabs">
            <button class="aipq-tab active" data-filter="all">
                <?php esc_html_e('Tất cả', 'whp'); ?> <span class="aipq-cnt"><?php echo $n; ?></span>
            </button>
            <button class="aipq-tab tab-high" data-filter="high">
                <svg width="9" height="9" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="#ef4444"/></svg>
                <?php esc_html_e('Rủi ro cao', 'whp'); ?> <span class="aipq-cnt"><?php echo $n_high; ?></span>
            </button>
            <button class="aipq-tab tab-med" data-filter="medium">
                <svg width="9" height="9" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="#f97316"/></svg>
                <?php esc_html_e('Rủi ro trung bình', 'whp'); ?> <span class="aipq-cnt"><?php echo $n_med; ?></span>
            </button>
            <button class="aipq-tab tab-low" data-filter="low">
                <svg width="9" height="9" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="#22c55e"/></svg>
                <?php esc_html_e('Rủi ro thấp', 'whp'); ?> <span class="aipq-cnt"><?php echo $n_low; ?></span>
            </button>
            <button class="aipq-tab tab-late" data-filter="late">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php esc_html_e('Chờ xử lý lâu', 'whp'); ?> <span class="aipq-cnt"><?php echo $n_late; ?></span>
            </button>
        </div>
        <form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>" id="aipq-filter-form" style="display:contents">
            <input type="hidden" name="page"       value="mb-wphelper-ai">
            <input type="hidden" name="subtab"     value="ai-payment">
            <input type="hidden" name="aipay_tab"  value="queue">
            <div class="aipq-toolbar">
                <div class="aipq-daterange-wrap">
                    <div class="aipq-daterange<?php echo ($f_from_raw || $f_to_raw) ? ' open' : ''; ?>" id="aipq-daterange-trigger">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span id="aipq-date-label"><?php echo esc_html( $date_from . ' – ' . $date_to ); ?></span>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="aipq-date-popover" id="aipq-date-popover">
                        <div class="aipq-date-row">
                            <label><?php esc_html_e('Từ ngày', 'whp'); ?></label>
                            <input type="date" name="aipq_from" id="aipq-inp-from"
                                   value="<?php echo esc_attr( $f_from_raw ); ?>">
                        </div>
                        <div class="aipq-date-row">
                            <label><?php esc_html_e('Đến ngày', 'whp'); ?></label>
                            <input type="date" name="aipq_to" id="aipq-inp-to"
                                   value="<?php echo esc_attr( $f_to_raw ); ?>">
                        </div>
                        <div class="aipq-date-actions">
                            <button type="button" class="aipq-date-clear" id="aipq-date-clear"><?php esc_html_e('Xóa', 'whp'); ?></button>
                            <button type="submit" class="aipq-date-apply" form="aipq-filter-form"><?php esc_html_e('Áp dụng', 'whp'); ?></button>
                        </div>
                    </div>
                </div>
                <button type="submit" class="aipq-tool-btn" form="aipq-filter-form">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <?php esc_html_e('Bộ lọc', 'whp'); ?>
                </button>
                <button type="button" class="aipq-tool-btn icon-only" id="aipq-refresh" title="<?php esc_attr_e('Làm mới', 'whp'); ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.32"/></svg>
                </button>
            </div>
        </form>
    </div>

    <!-- ── 2-column grid ───────────────────────────────────────── -->
    <div class="aipq-grid">

        <!-- ── Table column ── -->
        <div>
            <div class="aipq-card">
                <?php if ($n > 0): ?>
                <table class="aipq-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="aipq-chk" id="aipq-chk-all"></th>
                            <th><?php esc_html_e('Đơn hàng', 'whp'); ?></th>
                            <th><?php esc_html_e('Khách hàng', 'whp'); ?></th>
                            <th><?php esc_html_e('Số tiền', 'whp'); ?></th>
                            <th><?php esc_html_e('Ngân hàng', 'whp'); ?></th>
                            <th><?php esc_html_e('Thời gian CK', 'whp'); ?></th>
                            <th><?php esc_html_e('Rủi ro', 'whp'); ?></th>
                            <th><?php esc_html_e('Trạng thái', 'whp'); ?></th>
                            <th><?php esc_html_e('Thao tác', 'whp'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="aipq-tbody">
                    <?php foreach ($all_rows as $row):
                        $order    = $row['order'];
                        $oid      = $order->get_id();
                        $name     = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) ?: __('Khách', 'whp');
                        $phone    = $order->get_billing_phone() ?: '';
                        $total_f  = number_format(floatval($order->get_total()), 0, ',', '.') . 'đ';
                        $bank     = $order->get_meta('_whp_transfer_bank') ?: '—';
                        $time_f   = $row['conf_at'] ? date_i18n('d/m/Y H:i', strtotime($row['conf_at'])) : '—';
                        $odate_f  = $order->get_date_created() ? $order->get_date_created()->date_i18n('d/m/Y H:i') : '';
                        $rp       = $row['risk_pct'];
                        $rl       = $row['risk_lvl'];
                        $dc       = $rl==='high' ? 'aipq-rdot-high' : ($rl==='medium' ? 'aipq-rdot-med' : 'aipq-rdot-low');
                        $tc       = $rl==='high' ? 'risk-high-c'   : ($rl==='medium' ? 'risk-med-c'    : 'risk-low-c');
                        $detail_url = esc_url(admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=verify&order_id=' . $oid));
                    ?>
                    <tr class="aipq-row" data-risk="<?php echo esc_attr($rl); ?>" data-late="<?php echo $row['is_late'] ? '1' : '0'; ?>" data-oid="<?php echo $oid; ?>">
                        <td><input type="checkbox" class="aipq-chk aipq-row-chk" value="<?php echo $oid; ?>"></td>
                        <td>
                            <div class="aipq-oid">#<?php echo $oid; ?></div>
                            <?php if ($odate_f): ?>
                            <div class="aipq-odate"><?php echo esc_html($odate_f); ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="aipq-name"><?php echo esc_html($name); ?></div>
                            <?php if ($phone): ?>
                            <div class="aipq-phone"><?php echo esc_html($phone); ?></div>
                            <?php endif; ?>
                        </td>
                        <td><div class="aipq-amt"><?php echo esc_html($total_f); ?></div></td>
                        <td><div class="aipq-bank"><?php echo esc_html($bank); ?></div></td>
                        <td><div class="aipq-time"><?php echo esc_html($time_f); ?></div></td>
                        <td>
                            <span class="aipq-risk <?php echo $tc; ?>">
                                <span class="aipq-rdot <?php echo $dc; ?>"></span>
                                <?php echo $rp; ?>%
                            </span>
                        </td>
                        <td><span class="aipq-badge"><?php esc_html_e('Chờ xử lý', 'whp'); ?></span></td>
                        <td>
                            <div class="aipq-actions">
                                <button class="aipq-action-btn aipq-eye-btn" data-oid="<?php echo $oid; ?>" type="button" title="<?php esc_attr_e('Xem chi tiết', 'whp'); ?>">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                </button>
                                <button class="aipq-action-btn aipq-dd-trigger" data-oid="<?php echo $oid; ?>" data-url="<?php echo $detail_url; ?>" title="<?php esc_attr_e('Thao tác', 'whp'); ?>">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="aipq-no-result" id="aipq-no-result"><?php esc_html_e('Không có đơn nào phù hợp bộ lọc.', 'whp'); ?></div>
                <?php else: ?>
                <div class="aipq-empty">
                    <div style="font-size:38px;margin-bottom:10px">✅</div>
                    <div style="font-size:15px;font-weight:700;color:#0f172a;margin-bottom:6px"><?php esc_html_e('Không có đơn chờ xử lý', 'whp'); ?></div>
                    <div style="font-size:13px;color:#94a3b8"><?php esc_html_e('Tất cả đơn hàng đã được xác minh hoặc chưa có chuyển khoản nào.', 'whp'); ?></div>
                </div>
                <?php endif; ?>
                <!-- Pagination -->
                <div class="aipq-pag" id="aipq-pag">
                    <div id="aipq-pag-info" style="white-space:nowrap"><?php printf( esc_html__('Hiện thị 0/%s đơn', 'whp'), $n ); ?></div>
                    <div class="aipq-pag-btns" id="aipq-pag-btns"></div>
                    <div class="aipq-pag-size">
                        <span class="aipq-pag-size-lbl"><?php esc_html_e('Hiện thị', 'whp'); ?></span>
                        <select class="aipq-pag-sel" id="aipq-per-page">
                            <option value="8" selected>8</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="aipq-pag-size-unit"><?php esc_html_e('đơn', 'whp'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Right sidebar ── -->
        <div>

            <!-- Tóm tắt xử lý -->
            <div class="aipq-scard">
                <div class="aipq-sc-title"><?php esc_html_e('Tóm tắt xử lý', 'whp'); ?></div>
                <div class="aipq-donut-wrap">
                    <div class="aipq-donut" style="background:<?php
                        if ($n > 0):
                            echo 'conic-gradient(#ef4444 0deg ' . $dh . 'deg,#f97316 ' . $dh . 'deg ' . ($dh+$dm) . 'deg,#22c55e ' . ($dh+$dm) . 'deg 360deg)';
                        else:
                            echo '#e2e8f0';
                        endif;
                    ?>">
                        <div class="aipq-donut-hole">
                            <span class="aipq-donut-tot"><?php echo $n; ?></span>
                        </div>
                    </div>
                    <div class="aipq-legend">
                        <div class="aipq-leg-item">
                            <span class="aipq-leg-dot" style="background:#ef4444"></span>
                            <span><?php esc_html_e('Rủi ro cao', 'whp'); ?></span>
                            <span class="aipq-leg-val"><?php echo $n_high; ?> (<?php echo $ph; ?>%)</span>
                        </div>
                        <div class="aipq-leg-item">
                            <span class="aipq-leg-dot" style="background:#f97316"></span>
                            <span><?php esc_html_e('Rủi ro trung bình', 'whp'); ?></span>
                            <span class="aipq-leg-val"><?php echo $n_med; ?> (<?php echo $pm; ?>%)</span>
                        </div>
                        <div class="aipq-leg-item">
                            <span class="aipq-leg-dot" style="background:#22c55e"></span>
                            <span><?php esc_html_e('Rủi ro thấp', 'whp'); ?></span>
                            <span class="aipq-leg-val"><?php echo $n_low; ?> (<?php echo $pl; ?>%)</span>
                        </div>
                    </div>
                </div>
                <button type="button" id="aipq-bulk-verify-btn" class="aipq-bulk-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    <?php esc_html_e('Xác minh AI hàng loạt', 'whp'); ?>
                </button>
            </div>

            <!-- Kênh thanh toán -->
            <div class="aipq-scard">
                <div class="aipq-sc-title"><?php esc_html_e('Kênh thanh toán', 'whp'); ?></div>
                <div class="aipq-chan-row">
                    <span class="aipq-chan-lbl"><?php esc_html_e('Chuyển khoản ngân hàng', 'whp'); ?></span>
                    <div class="aipq-chan-bar"><div class="aipq-chan-fill" style="width:<?php echo $pb; ?>%;background:#e11d48"></div></div>
                    <span class="aipq-chan-cnt"><?php echo $n_bank; ?></span>
                    <span class="aipq-chan-pct">(<?php echo $pb; ?>%)</span>
                </div>
                <div class="aipq-chan-row">
                    <span class="aipq-chan-lbl"><?php esc_html_e('Ví điện tử', 'whp'); ?></span>
                    <div class="aipq-chan-bar"><div class="aipq-chan-fill" style="width:<?php echo $pw; ?>%;background:#94a3b8"></div></div>
                    <span class="aipq-chan-cnt"><?php echo $n_wallet; ?></span>
                    <span class="aipq-chan-pct">(<?php echo $pw; ?>%)</span>
                </div>
            </div>

            <!-- Gợi ý tối ưu AI -->
            <div class="aipq-scard">
                <div class="aipq-sc-title"><?php esc_html_e('Gợi ý tối ưu AI', 'whp'); ?></div>
                <a href="<?php echo esc_url($config_url); ?>" class="aipq-sug-item">
                    <span class="aipq-sug-ico blue">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M3 9h3M3 15h3M18 9h3M18 15h3M9 3v3M15 3v3M9 18v3M15 18v3"/></svg>
                    </span>
                    <div class="aipq-sug-body">
                        <div class="aipq-sug-title"><?php esc_html_e('Bật AI OCR quét biên lai', 'whp'); ?></div>
                        <div class="aipq-sug-desc"><?php esc_html_e('Tự động đọc & xác minh ảnh chuyển khoản', 'whp'); ?></div>
                    </div>
                    <svg class="aipq-sug-arr" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="<?php echo esc_url($notif_url); ?>" class="aipq-sug-item">
                    <span class="aipq-sug-ico amber">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </span>
                    <div class="aipq-sug-body">
                        <div class="aipq-sug-title"><?php esc_html_e('Cài đặt thông báo đa kênh', 'whp'); ?></div>
                        <div class="aipq-sug-desc"><?php esc_html_e('Nhận alert khi có đơn mới qua Discord, Webhook', 'whp'); ?></div>
                    </div>
                    <svg class="aipq-sug-arr" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>

        </div><!-- /sidebar -->
    </div><!-- /grid -->
</div><!-- /outer -->

<!-- Dropdown menu -->
<div class="aipq-dd-menu" id="aipq-dd">
    <button class="aipq-dd-item" id="aipq-dd-view">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <?php esc_html_e('Xem chi tiết', 'whp'); ?>
    </button>
    <button class="aipq-dd-item" id="aipq-dd-ai" style="color:#059669">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
        <?php esc_html_e('Xác minh AI', 'whp'); ?>
    </button>
    <div class="aipq-dd-sep"></div>
    <button class="aipq-dd-item" id="aipq-dd-ok" style="color:#059669">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <?php esc_html_e('Xác nhận thanh toán', 'whp'); ?>
    </button>
    <button class="aipq-dd-item" id="aipq-dd-no" style="color:#dc2626">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        <?php esc_html_e('Từ chối', 'whp'); ?>
    </button>
</div>

<script>
var whpI18n = <?php echo wp_json_encode([
    'showingOrders'        => __( 'Hiện thị {start}/{total} đơn', 'whp' ),
    'verifyingAI'          => __( 'Đang xác minh AI…', 'whp' ),
    'aiVerifyComplete'     => __( 'Xác minh AI hoàn tất!', 'whp' ),
    'cannotVerifyNoReceipt'=> __( 'Không thể xác minh. Đơn hàng có thể chưa có biên lai.', 'whp' ),
    'connectionFailedRetry'=> __( 'Kết nối thất bại. Thử lại.', 'whp' ),
    'paymentConfirmed'     => __( 'Đã xác nhận thanh toán thành công!', 'whp' ),
    'orderRejected'        => __( 'Đã từ chối đơn hàng.', 'whp' ),
    'markedSuspicious'     => __( 'Đã đánh dấu nghi ngờ.', 'whp' ),
    'errorOccurredRetry'   => __( 'Có lỗi xảy ra. Thử lại.', 'whp' ),
    'confirmPaymentOrder'  => __( 'Xác nhận thanh toán đơn', 'whp' ),
    'rejectOrder'          => __( 'Từ chối đơn', 'whp' ),
    'noOrdersToVerify'     => __( 'Không có đơn nào để xác minh.', 'whp' ),
    'confirmBulkVerify'    => __( 'Xác minh AI {n} đơn? Quá trình này có thể mất vài phút.', 'whp' ),
    'bulkVerifyDone'       => __( 'Hoàn tất! {done}/{total} đơn đã xác minh', 'whp' ),
    'failed'               => __( 'thất bại', 'whp' ),
    'verifyAllDone'        => __( 'Hoàn tất xác minh {total} đơn', 'whp' ),
    'verifyingProgress'    => __( 'Đang xác minh {current}/{total}…', 'whp' ),
    'loading'              => __( 'Đang tải…', 'whp' ),
    'loadDataFailed'       => __( 'Không tải được dữ liệu.', 'whp' ),
    'connectionFailed'     => __( 'Kết nối thất bại.', 'whp' ),
    'riskHigh'             => __( 'Rủi ro cao', 'whp' ),
    'riskMedium'           => __( 'Rủi ro trung bình', 'whp' ),
    'riskLow'              => __( 'Rủi ro thấp', 'whp' ),
    'noReceiptImage'       => __( 'Chưa có ảnh biên lai', 'whp' ),
    'checkAmountMatch'     => __( 'Số tiền khớp đơn hàng', 'whp' ),
    'checkAccountCorrect'  => __( 'Tài khoản nhận tiền đúng', 'whp' ),
    'checkBankMatch'       => __( 'Ngân hàng/ví khớp', 'whp' ),
    'checkTimeValid'       => __( 'Thời gian giao dịch hợp lệ', 'whp' ),
    'checkTransferContentMatch' => __( 'Nội dung chuyển khoản khớp', 'whp' ),
    'checkNoTampering'     => __( 'Biên lai không có dấu hiệu chỉnh sửa', 'whp' ),
    'ocrConfidence'        => __( 'Độ tin cậy OCR', 'whp' ),
    'verdictValid'         => __( '✓ Biên lai hợp lệ', 'whp' ),
    'verdictSuspicious'    => __( '⚠ Nghi ngờ gian lận', 'whp' ),
    'verdictInvalid'       => __( '✗ Biên lai không hợp lệ', 'whp' ),
    'noAnalysisResult'     => __( 'Chưa có kết quả phân tích', 'whp' ),
    'orderId'              => __( 'Mã đơn', 'whp' ),
    'customer'             => __( 'Khách hàng', 'whp' ),
    'phone'                => __( 'SĐT', 'whp' ),
    'orderValue'           => __( 'Giá trị đơn', 'whp' ),
    'status'               => __( 'Trạng thái', 'whp' ),
    'confirmPayment'       => __( 'Xác nhận thanh toán', 'whp' ),
    'rejectOrderLong'      => __( 'Từ chối đơn hàng', 'whp' ),
    'noteSaved'            => __( 'Đã lưu ghi chú.', 'whp' ),
    'actionSuccess'        => __( 'Thao tác thành công!', 'whp' ),
    'errorOccurred'        => __( 'Có lỗi xảy ra.', 'whp' ),
    'markSuspicious'       => __( 'Đánh dấu nghi ngờ', 'whp' ),
    'rejectPayment'        => __( 'Từ chối thanh toán', 'whp' ),
    'requestReceipt'       => __( 'Yêu cầu gửi lại biên lai', 'whp' ),
    'paymentConfirmed2'    => __( 'Đã xác nhận thanh toán!', 'whp' ),
    'paymentRejected'      => __( 'Đã từ chối thanh toán.', 'whp' ),
    'receiptRequestSent'   => __( 'Đã gửi yêu cầu biên lai tới khách!', 'whp' ),
    'emailNotSent'         => __( 'Lưu ý: KHÔNG gửi được email — đơn hàng thiếu email khách hàng.', 'whp' ),
]); ?>;
(function(){
var rows      = Array.from(document.querySelectorAll('#aipq-tbody .aipq-row'));
var curFilter = 'all';
var perPage   = 8;
var curPage   = 1;
var nonce     = <?php echo wp_json_encode($ajax_nonce); ?>;

// Filter tabs
document.querySelectorAll('.aipq-tab').forEach(function(btn){
    btn.addEventListener('click', function(){
        document.querySelectorAll('.aipq-tab').forEach(function(b){ b.classList.remove('active'); });
        btn.classList.add('active');
        curFilter = btn.dataset.filter;
        curPage = 1;
        render();
    });
});

// Per-page
var ppSel = document.getElementById('aipq-per-page');
if (ppSel) ppSel.addEventListener('change', function(){ perPage = parseInt(this.value,10); curPage=1; render(); });

function getVisible(){
    return rows.filter(function(tr){
        if (curFilter === 'high')   return tr.dataset.risk === 'high';
        if (curFilter === 'medium') return tr.dataset.risk === 'medium';
        if (curFilter === 'low')    return tr.dataset.risk === 'low';
        if (curFilter === 'late')   return tr.dataset.late === '1';
        return true;
    });
}

function render(){
    var vis   = getVisible();
    var total = vis.length;
    var pages = Math.max(1, Math.ceil(total / perPage));
    curPage   = Math.min(curPage, pages);
    rows.forEach(function(tr){ tr.style.display = 'none'; });
    vis.slice((curPage-1)*perPage, curPage*perPage).forEach(function(tr){ tr.style.display = ''; });
    var noRes = document.getElementById('aipq-no-result');
    if (noRes) noRes.style.display = total===0 ? 'block' : 'none';
    var info = document.getElementById('aipq-pag-info');
    var _start = (curPage-1)*perPage+1, _end = Math.min(perPage*curPage, total);
    if (info) info.textContent = whpI18n.showingOrders.replace('{start}', _start).replace('{end}', _end).replace('{total}', total);
    buildPag(pages);
}

function buildPag(pages){
    var wrap = document.getElementById('aipq-pag-btns');
    if (!wrap) return;
    wrap.innerHTML = '';
    function btn(lbl, page, dis, act){
        var b = document.createElement('button');
        b.className = 'aipq-pag-btn' + (act?' active':'');
        b.textContent = lbl;
        b.disabled = dis;
        if (!dis) b.addEventListener('click', function(){ curPage=page; render(); });
        return b;
    }
    wrap.appendChild(btn('❮', curPage-1, curPage===1, false));
    var s = Math.max(1, curPage-2), e = Math.min(pages, s+4);
    for (var p=s; p<=e; p++) wrap.appendChild(btn(String(p), p, false, p===curPage));
    wrap.appendChild(btn('❯', curPage+1, curPage===pages, false));
}

render();

// Check-all
var chkAll = document.getElementById('aipq-chk-all');
if (chkAll) chkAll.addEventListener('change', function(){
    document.querySelectorAll('.aipq-row-chk').forEach(function(c){ c.checked = chkAll.checked; });
});

// Refresh
var ref = document.getElementById('aipq-refresh');
if (ref) ref.addEventListener('click', function(){ location.reload(); });

// Date range popover
(function(){
    var trigger = document.getElementById('aipq-daterange-trigger');
    var popover = document.getElementById('aipq-date-popover');
    if (!trigger || !popover) return;
    trigger.addEventListener('click', function(e){
        e.stopPropagation();
        var isOpen = popover.classList.contains('open');
        popover.classList.toggle('open', !isOpen);
        trigger.classList.toggle('open', !isOpen);
    });
    document.addEventListener('click', function(e){
        if (!trigger.closest('.aipq-daterange-wrap').contains(e.target)) {
            popover.classList.remove('open');
            trigger.classList.remove('open');
        }
    });
    var clearBtn = document.getElementById('aipq-date-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function(){
            document.getElementById('aipq-inp-from').value = '';
            document.getElementById('aipq-inp-to').value = '';
            document.getElementById('aipq-filter-form').submit();
        });
    }
    // Update label when dates change
    var inpFrom = document.getElementById('aipq-inp-from');
    var inpTo   = document.getElementById('aipq-inp-to');
    var label   = document.getElementById('aipq-date-label');
    function updateLabel(){
        var f = inpFrom.value, t = inpTo.value;
        if (!f && !t) return;
        function fmt(d){ if(!d) return ''; var p=d.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }
        label.textContent = (f ? fmt(f) : '…') + ' – ' + (t ? fmt(t) : '…');
    }
    if (inpFrom) inpFrom.addEventListener('change', updateLabel);
    if (inpTo)   inpTo.addEventListener('change', updateLabel);
})();

// Dropdown
var dd    = document.getElementById('aipq-dd');
var ddOid = null, ddUrl = null;

document.querySelectorAll('.aipq-dd-trigger').forEach(function(btn){
    btn.addEventListener('click', function(e){
        e.stopPropagation();
        ddOid = btn.dataset.oid;
        ddUrl = btn.dataset.url;
        var r = btn.getBoundingClientRect();
        var menuH = 150;
        var top = (window.innerHeight - r.bottom < menuH) ? (r.top - menuH - 4) : (r.bottom + 4);
        dd.style.top  = top + 'px';
        dd.style.left = (r.right - 160) + 'px';
        dd.style.display = 'block';
    });
});
document.addEventListener('click', function(){ if(dd) dd.style.display='none'; });
if(dd) dd.addEventListener('click', function(e){ e.stopPropagation(); });

// aipq-dd-view handled via popup delegation below
document.getElementById('aipq-dd-ai')?.addEventListener('click', function(){
    if (!ddOid) return;
    dd.style.display = 'none';
    var btn = document.querySelector('.aipq-dd-trigger[data-oid="'+ddOid+'"]');
    var origHtml = btn ? btn.innerHTML : '';
    if (btn) btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
    wpaapToast(whpI18n.verifyingAI, 'info');
    jQuery.post(ajaxurl, { action:'wpaap_aipay_verify', nonce:nonce, order_id:ddOid }, function(res){
        if (btn) btn.innerHTML = origHtml;
        if (res && res.success) {
            wpaapToast(whpI18n.aiVerifyComplete, 'success');
            setTimeout(function(){ location.reload(); }, 1500);
        } else {
            wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.cannotVerifyNoReceipt, 'error');
        }
    }).fail(function(){
        if (btn) btn.innerHTML = origHtml;
        wpaapToast(whpI18n.connectionFailedRetry, 'error');
    });
});

function doAction(type, confirmMsg){
    if (!ddOid) return;
    dd.style.display = 'none';
    var _oid = ddOid;
    wpaapConfirm(confirmMsg + ' #' + _oid + '?', function(){
        wpaapToast(whpI18n.verifyingAI, 'info');
        var successMsgs = {confirm: whpI18n.paymentConfirmed, reject: whpI18n.orderRejected, suspect: whpI18n.markedSuspicious};
        jQuery.post(ajaxurl, { action:'wpaap_aipay_order_action', nonce:nonce, order_id:_oid, type:type }, function(res){
            if (res && res.success) {
                wpaapToast(successMsgs[type] || whpI18n.actionSuccess, 'success');
                var _noEmail = res.data && res.data.email_sent === false;
                if (_noEmail) {
                    setTimeout(function(){ wpaapToast(whpI18n.emailNotSent, 'warning'); }, 400);
                }
                setTimeout(function(){ location.reload(); }, _noEmail ? 3200 : 1500);
            } else {
                wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.errorOccurredRetry, 'error');
            }
        }).fail(function(){
            wpaapToast(whpI18n.connectionFailedRetry, 'error');
        });
    });
}
document.getElementById('aipq-dd-ok')?.addEventListener('click', function(){ doAction('confirm', whpI18n.confirmPaymentOrder); });
document.getElementById('aipq-dd-no')?.addEventListener('click', function(){ doAction('reject',  whpI18n.rejectOrder); });

// ── Bulk AI verify ────────────────────────────────────────────
var bulkBtn = document.getElementById('aipq-bulk-verify-btn');
if (bulkBtn) {
    bulkBtn.addEventListener('click', function() {
        var checked = Array.from(document.querySelectorAll('.aipq-row-chk:checked')).map(function(c){ return c.value; });
        if (!checked.length) {
            checked = Array.from(document.querySelectorAll('.aipq-row-chk')).map(function(c){ return c.value; });
        }
        if (!checked.length) { wpaapToast(whpI18n.noOrdersToVerify, 'warning'); return; }
        wpaapConfirm(whpI18n.confirmBulkVerify.replace('{n}', checked.length), function(){
        var total = checked.length, done = 0, failed = 0;
        bulkBtn.disabled = true;

        function verifyNext() {
            if (done + failed >= total) {
                // Visual success state on the button itself
                if (failed === 0) {
                    bulkBtn.style.cssText = 'background:#dcfce7;border-color:#86efac;color:#15803d;';
                    bulkBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> ' + whpI18n.bulkVerifyDone.replace('{done}', done).replace('{total}', total);
                } else {
                    bulkBtn.style.cssText = 'background:#fef9c3;border-color:#fde047;color:#854d0e;';
                    bulkBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> ' + done + ' OK, ' + failed + ' ' + whpI18n.failed;
                }
                bulkBtn.disabled = false;
                // Toast notification
                var msg = whpI18n.verifyAllDone.replace('{total}', total);
                if (failed > 0) msg += ' (' + done + ' OK, ' + failed + ' ' + whpI18n.failed + ')';
                else msg += ' thành công!';
                if (typeof window.wpaapToast === 'function') window.wpaapToast(msg, failed > 0 ? 'warning' : 'success');
                setTimeout(function(){ location.reload(); }, 2500);
                return;
            }
            var idx = done + failed;
            var oid = checked[idx];
            bulkBtn.innerHTML = '<svg class="rskp-spinner" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> ' + whpI18n.verifyingProgress.replace('{current}', idx+1).replace('{total}', total);
            jQuery.post(ajaxurl, { action: 'wpaap_aipay_verify', nonce: nonce, order_id: oid }, function(res) {
                if (res && res.success) done++; else failed++;
                verifyNext();
            }).fail(function() {
                failed++;
                verifyNext();
            });
        }
        verifyNext();
        }); // end wpaapConfirm
    });
}

// ── Order detail popup ────────────────────────────────────────
var rskpNonce  = nonce;
var rskpActive = null;

function rskpOpen(orderId) {
    rskpActive = orderId;
    document.getElementById('rskp-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    rskpLoadDetail(orderId);
}
function rskpClose() {
    document.getElementById('rskp-overlay').classList.remove('open');
    document.body.style.overflow = '';
    rskpActive = null;
}
function rskpLoadDetail(orderId) {
    document.getElementById('rskp-title').textContent = '#' + orderId + ' – …';
    document.getElementById('rskp-risk-badge').textContent = '';
    document.getElementById('rskp-risk-badge').className = 'rskp-risk-badge';
    ['rskp-d-confirmed','rskp-d-payment','rskp-d-total','rskp-d-sender'].forEach(function(id){
        document.getElementById(id).textContent = '…';
    });
    document.getElementById('rskp-receipt-wrap').innerHTML = '<div class="rskp-receipt-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg></div>';
    document.getElementById('rskp-receipt-actions').style.display = 'none';
    document.getElementById('rskp-checklist-wrap').innerHTML = '<div style="color:#94a3b8;font-size:12px;text-align:center;padding:20px 0">' + whpI18n.loading + '</div>';
    document.getElementById('rskp-verdict-section').style.display = 'none';
    document.getElementById('rskp-oi-rows').innerHTML = '';
    jQuery.post(ajaxurl, {action:'wpaap_aipay_get_order_detail', nonce:rskpNonce, order_id:orderId}, function(res) {
        if (!res || !res.success) {
            document.getElementById('rskp-checklist-wrap').innerHTML = '<div style="color:#dc2626;font-size:12px;text-align:center;padding:16px">' + whpI18n.loadDataFailed + '</div>';
            return;
        }
        rskpPopulate(res.data);
    }).fail(function(){
        document.getElementById('rskp-checklist-wrap').innerHTML = '<div style="color:#dc2626;font-size:12px;text-align:center;padding:16px">' + whpI18n.connectionFailed + '</div>';
    });
}
function rskpPopulate(d) {
    document.getElementById('rskp-title').textContent = '#' + d.order_id + ' – ' + d.name;
    var ai = d.ai_result, riskPct = 0, lvl = '';
    if (ai && ai.risk_score) {
        riskPct = Math.round(parseFloat(ai.risk_score) * 100);
        lvl = riskPct >= 65 ? 'high' : (riskPct >= 35 ? 'medium' : 'low');
        var lbl = {high: whpI18n.riskHigh, medium: whpI18n.riskMedium, low: whpI18n.riskLow};
        var badge = document.getElementById('rskp-risk-badge');
        badge.textContent = '● ' + riskPct + '% ' + lbl[lvl];
        badge.className = 'rskp-risk-badge ' + lvl;
    }
    document.getElementById('rskp-d-confirmed').textContent = d.confirmed;
    document.getElementById('rskp-d-payment').textContent   = d.payment;
    var totalEl = document.getElementById('rskp-d-total');
    totalEl.textContent = d.total; totalEl.className = 'rskp-meta-val amount';
    document.getElementById('rskp-d-sender').textContent = d.sender;
    var receiptWrap = document.getElementById('rskp-receipt-wrap');
    if (d.receipt) {
        receiptWrap.innerHTML = '<img class="rskp-receipt-img" id="rskp-img" src="' + d.receipt + '" alt="Biên lai">';
        document.getElementById('rskp-receipt-actions').style.display = 'flex';
        document.getElementById('rskp-btn-view-orig').href  = d.receipt;
        document.getElementById('rskp-btn-download').href   = d.receipt;
        document.getElementById('rskp-btn-zoom').onclick    = function(){ rskpLightbox(d.receipt); };
        document.getElementById('rskp-img').onclick = function(){ rskpLightbox(d.receipt); };
    } else {
        receiptWrap.innerHTML = '<div class="rskp-receipt-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="13" y2="13"/></svg><span>' + whpI18n.noReceiptImage + '</span></div>';
    }
    document.getElementById('rskp-verify-btn').dataset.oid = d.order_id;
    var cl = document.getElementById('rskp-checklist-wrap');
    function rskpCheckItem(pass, label, val) {
        var ok = pass ? 'pass' : 'fail';
        return '<div class="rskp-check-item"><div class="rskp-check-icon ' + ok + '">' + (pass ? '✓' : '✗') + '</div>'
             + '<span class="rskp-check-label">' + escHp(label) + '</span>'
             + (val ? '<span class="rskp-check-val ' + ok + '">' + escHp(val) + '</span>' : '') + '</div>';
    }
    // Build checklist: ưu tiên ai.checks (array), fallback flat fields từ AI Vision
    var hasResult = false;
    if (ai) {
        var html = '<div class="rskp-checklist">';
        if (ai.checks && ai.checks.length) {
            ai.checks.forEach(function(c) {
                html += rskpCheckItem(c.passed, c.label||c.check||'', c.value||'');
            });
            hasResult = true;
        } else {
            // Flat fields từ AI Vision (format chuẩn của plugin)
            if (typeof ai.amount_match !== 'undefined') { html += rskpCheckItem(ai.amount_match, whpI18n.checkAmountMatch, ai.amount ? String(ai.amount).replace(/\B(?=(\d{3})+(?!\d))/g,'.') + ' VND' : ''); hasResult = true; }
            if (typeof ai.account_match !== 'undefined') { html += rskpCheckItem(ai.account_match, whpI18n.checkAccountCorrect, ai.account_to || ''); hasResult = true; }
            if (typeof ai.bank_match !== 'undefined') { html += rskpCheckItem(ai.bank_match, whpI18n.checkBankMatch, ai.bank || ''); hasResult = true; }
            if (typeof ai.time_match !== 'undefined') { html += rskpCheckItem(ai.time_match, whpI18n.checkTimeValid, (ai.date||'') + (ai.time ? ' '+ai.time : '')); hasResult = true; }
            if (typeof ai.note_match !== 'undefined') { html += rskpCheckItem(ai.note_match, whpI18n.checkTransferContentMatch, ai.note || ''); hasResult = true; }
            if (typeof ai.edited !== 'undefined') { html += rskpCheckItem(!ai.edited, whpI18n.checkNoTampering, ''); hasResult = true; }
            if (ai.risk_flags && ai.risk_flags.length) {
                ai.risk_flags.forEach(function(f){ html += rskpCheckItem(false, f, ''); });
            }
        }
        // Confidence bar
        var confVal = ai.ocr_confidence != null ? ai.ocr_confidence : (ai.confidence != null ? ai.confidence : null);
        if (confVal != null) {
            var conf = Math.round(parseFloat(confVal) * (parseFloat(confVal) <= 1 ? 100 : 1));
            html += '<div style="display:flex;align-items:center;gap:8px;padding-top:8px;margin-top:6px;border-top:1px solid #f1f5f9;">'
                  + '<span style="flex:1;font-size:12.5px;color:#374151;">' + whpI18n.ocrConfidence + '</span>'
                  + '<div style="width:50px;height:5px;background:#e2e8f0;border-radius:6px;overflow:hidden"><div style="height:100%;width:'+conf+'%;background:#10b981;border-radius:6px;"></div></div>'
                  + '<span style="font-size:12px;font-weight:700;color:#059669;min-width:28px;text-align:right">'+conf+'%</span></div>';
        }
        html += '</div>';
        if (hasResult) {
            cl.innerHTML = html;
            if (ai.verdict_reason || ai.verdict) {
                document.getElementById('rskp-verdict-section').style.display = 'block';
                document.getElementById('rskp-verdict-reason').textContent = ai.verdict_reason || '';
                var vv = ai.verdict || '';
                var vmap = {valid: whpI18n.verdictValid, suspicious: whpI18n.verdictSuspicious, invalid: whpI18n.verdictInvalid};
                document.getElementById('rskp-verdict-badge').textContent = vmap[vv] || vv;
                document.getElementById('rskp-verdict-badge').className   = 'rskp-verdict-badge ' + vv;
            }
        } else {
            cl.innerHTML = '<div style="color:#94a3b8;font-size:12px;text-align:center;padding:20px 0">' + whpI18n.noAnalysisResult + '</div>';
        }
    } else {
        cl.innerHTML = '<div style="color:#94a3b8;font-size:12px;text-align:center;padding:20px 0">' + whpI18n.noAnalysisResult + '</div>';
    }
    var oi = document.getElementById('rskp-oi-rows');
    var status = d.status || '';
    var oiRows = [[whpI18n.orderId,'#'+d.order_id,''],[whpI18n.customer,d.name,''],['Email',d.email,''],[whpI18n.phone,d.phone||'—',''],[whpI18n.orderValue,d.total,'amount'],[whpI18n.status,d.status_lbl,status]];
    oi.innerHTML = oiRows.map(function(r){ return '<div class="rskp-oi-row"><span class="rskp-oi-key">'+escHp(r[0])+'</span><span class="rskp-oi-val '+escHp(r[2])+'">'+escHp(r[1])+'</span></div>'; }).join('');
    document.querySelectorAll('.rskp-act-btn[data-type]').forEach(function(b){ b.dataset.oid = d.order_id; });
    document.getElementById('rskp-oi-link').href = d.order_url;
}
function escHp(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function rskpLightbox(src){ var lb=document.getElementById('rskp-lightbox'); document.getElementById('rskp-lb-img').src=src; lb.classList.add('open'); }

// Eye button
document.addEventListener('click', function(e){
    var eyeBtn = e.target.closest('.aipq-eye-btn');
    if (eyeBtn) { e.preventDefault(); rskpOpen(eyeBtn.dataset.oid); return; }
    // Dropdown "Xem chi tiết"
    if (e.target.closest('#aipq-dd-view')) { if(ddOid){ dd.style.display='none'; rskpOpen(ddOid); } return; }
    // Popup overlay
    if (e.target.id === 'rskp-overlay') { rskpClose(); return; }
    if (e.target.closest('#rskp-close-btn')) { rskpClose(); return; }
    if (e.target.closest('#rskp-lb-close')) { document.getElementById('rskp-lightbox').classList.remove('open'); return; }
    if (e.target.closest('#rskp-verify-btn')) {
        var vbtn = document.getElementById('rskp-verify-btn');
        var oid = vbtn && vbtn.dataset.oid; if (!oid) return;
        var origHtml = vbtn.innerHTML; vbtn.disabled = true;
        vbtn.innerHTML = '<svg class="rskp-spinner" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg> ' + whpI18n.verifyingAI;
        jQuery.post(ajaxurl, {action:'wpaap_aipay_verify', nonce:rskpNonce, order_id:oid}, function(res){
            vbtn.disabled = false; vbtn.innerHTML = origHtml;
            if (res && res.success) { rskpLoadDetail(oid); }
            else { wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.cannotVerifyNoReceipt, 'error'); }
        }).fail(function(){ vbtn.disabled=false; vbtn.innerHTML=origHtml; wpaapToast(whpI18n.connectionFailed, 'error'); });
        return;
    }
    var actBtn = e.target.closest('.rskp-act-btn[data-type]');
    if (actBtn) {
        var type = actBtn.dataset.type, oid = actBtn.dataset.oid || rskpActive;
        if (!oid) return;
        if (type === 'note') { var wrap=document.getElementById('rskp-note-wrap'); wrap.style.display=wrap.style.display==='none'?'block':'none'; return; }
        var msgs = {confirm: whpI18n.confirmPayment, suspect: whpI18n.markSuspicious, reject: whpI18n.rejectPayment, request_receipt: whpI18n.requestReceipt};
        var _oid = oid;
        var successMsgsPop = {confirm: whpI18n.paymentConfirmed2, suspect: whpI18n.markedSuspicious, reject: whpI18n.paymentRejected, request_receipt: whpI18n.receiptRequestSent};
        var _actBtn = actBtn;
        var _origHtml = actBtn.innerHTML;
        wpaapConfirm(msgs[type]+' đơn #'+_oid+'?', function(){
            _actBtn.disabled = true;
            _actBtn.innerHTML = '<svg style="animation:rskp-spin .8s linear infinite" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
            jQuery.post(ajaxurl, {action:'wpaap_aipay_order_action', nonce:rskpNonce, order_id:_oid, type:type}, function(res){
                _actBtn.disabled = false;
                _actBtn.innerHTML = _origHtml;
                if (res && res.success) {
                    wpaapToast(successMsgsPop[type] || whpI18n.actionSuccess, 'success');
                    var _noEmail = res.data && res.data.email_sent === false;
                    if (_noEmail) {
                        setTimeout(function(){ wpaapToast(whpI18n.emailNotSent, 'warning'); }, 400);
                    }
                    rskpLoadDetail(_oid);
                    setTimeout(function(){ location.reload(); }, _noEmail ? 3200 : 1500);
                } else {
                    wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.errorOccurred, 'error');
                }
            }).fail(function(){
                _actBtn.disabled = false;
                _actBtn.innerHTML = _origHtml;
                wpaapToast(whpI18n.connectionFailed, 'error');
            });
        });
        return;
    }
    if (e.target.closest('#rskp-note-submit')) {
        var oid = rskpActive, note = (document.getElementById('rskp-note-input').value||'').trim();
        if (!oid || !note) return;
        jQuery.post(ajaxurl, {action:'wpaap_aipay_order_action', nonce:rskpNonce, order_id:oid, type:'note', note:note}, function(res){
            if (res && res.success) { document.getElementById('rskp-note-input').value=''; document.getElementById('rskp-note-wrap').style.display='none'; wpaapToast(whpI18n.noteSaved, 'success'); }
        });
    }
});
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') { var lb=document.getElementById('rskp-lightbox'); if(lb&&lb.classList.contains('open')){lb.classList.remove('open');return;} rskpClose(); }
});

})();
</script>

<!-- ═══ Order detail popup ════════════════════════════════════════════ -->
<div class="rskp-overlay" id="rskp-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Chi tiết đơn hàng', 'whp'); ?>">
  <div class="rskp-modal">
    <div class="rskp-head">
      <div class="rskp-head-left">
        <span class="rskp-title" id="rskp-title">#— – —</span>
        <span class="rskp-risk-badge" id="rskp-risk-badge"></span>
      </div>
      <button class="rskp-close" id="rskp-close-btn" aria-label="<?php esc_attr_e('Đóng', 'whp'); ?>">×</button>
    </div>
    <div class="rskp-meta">
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Thời gian', 'whp'); ?></div><div class="rskp-meta-val" id="rskp-d-confirmed">—</div></div>
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Phương thức', 'whp'); ?></div><div class="rskp-meta-val" id="rskp-d-payment">—</div></div>
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Số tiền', 'whp'); ?></div><div class="rskp-meta-val amount" id="rskp-d-total">—</div></div>
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Người gửi', 'whp'); ?></div><div class="rskp-meta-val" id="rskp-d-sender">—</div></div>
    </div>
    <div class="rskp-body">
      <div class="rskp-main">
        <div class="rskp-receipt-panel">
          <div class="rskp-panel-hd"><?php esc_html_e('Ảnh biên lai (AI Vision)', 'whp'); ?></div>
          <div id="rskp-receipt-wrap"><div class="rskp-receipt-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div></div>
          <div class="rskp-receipt-actions" id="rskp-receipt-actions" style="display:none">
            <a class="rskp-receipt-btn" id="rskp-btn-view-orig" href="#" target="_blank"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg><?php esc_html_e('Xem ảnh gốc', 'whp'); ?></a>
            <a class="rskp-receipt-btn" id="rskp-btn-download" href="#" download><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg><?php esc_html_e('Tải ảnh', 'whp'); ?></a>
            <button class="rskp-receipt-btn" id="rskp-btn-zoom"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg><?php esc_html_e('Phóng to', 'whp'); ?></button>
          </div>
          <button class="rskp-btn-verify" id="rskp-verify-btn" data-oid=""><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg><?php esc_html_e('Xác minh bằng AI', 'whp'); ?></button>
        </div>
        <div class="rskp-ai-panel">
          <div class="rskp-panel-hd"><?php esc_html_e('Kết quả phân tích AI', 'whp'); ?></div>
          <div id="rskp-checklist-wrap"></div>
          <div id="rskp-verdict-section" style="display:none" class="rskp-verdict-section">
            <div class="rskp-panel-hd" style="margin-top:10px"><?php esc_html_e('Nhận định AI', 'whp'); ?></div>
            <div class="rskp-verdict-text" id="rskp-verdict-reason"></div>
            <span class="rskp-verdict-badge" id="rskp-verdict-badge"></span>
          </div>
        </div>
      </div>
      <div class="rskp-sidebar">
        <div class="rskp-sc">
          <div class="rskp-sc-title"><?php esc_html_e('Hành động nhanh', 'whp'); ?></div>
          <div class="rskp-act-btns">
            <button class="rskp-act-btn confirm" data-type="confirm"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><?php esc_html_e('Xác nhận thanh toán', 'whp'); ?></button>
            <button class="rskp-act-btn suspect" data-type="suspect"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg><?php esc_html_e('Nghi ngờ / Kiểm tra thêm', 'whp'); ?></button>
            <button class="rskp-act-btn reject" data-type="reject"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><?php esc_html_e('Từ chối thanh toán', 'whp'); ?></button>
            <button class="rskp-act-btn outline" data-type="request_receipt"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.63"/></svg><?php esc_html_e('Yêu cầu gửi lại biên lai', 'whp'); ?></button>
            <button class="rskp-act-btn outline" data-type="note"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg><?php esc_html_e('Ghi chú nội bộ', 'whp'); ?></button>
          </div>
          <div class="rskp-note-wrap" id="rskp-note-wrap">
            <textarea class="rskp-note-input" id="rskp-note-input" placeholder="<?php esc_attr_e('Nhập ghi chú nội bộ…', 'whp'); ?>"></textarea>
            <button class="rskp-note-submit" id="rskp-note-submit"><?php esc_html_e('Lưu ghi chú', 'whp'); ?></button>
          </div>
        </div>
        <div class="rskp-sc">
          <div class="rskp-sc-title"><?php esc_html_e('Thông tin đơn hàng', 'whp'); ?></div>
          <div class="rskp-oi-rows" id="rskp-oi-rows"></div>
          <a class="rskp-oi-link" id="rskp-oi-link" href="#" target="_blank"><?php esc_html_e('Xem chi tiết đơn hàng →', 'whp'); ?></a>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="rskp-lightbox" id="rskp-lightbox">
  <button class="rskp-lightbox-close" id="rskp-lb-close">×</button>
  <img id="rskp-lb-img" src="" alt="<?php esc_attr_e('Biên lai phóng to', 'whp'); ?>">
</div>
<?php
}
