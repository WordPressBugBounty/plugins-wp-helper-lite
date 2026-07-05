<?php defined('ABSPATH') || exit;

function wpaap_aipay_risk_layout() {
    // ─── Data ─────────────────────────────────────────────────────────────
    $active_filter = sanitize_key($_GET['risk_lvl'] ?? 'all');
    $date_from_raw = sanitize_text_field($_GET['date_from'] ?? '');
    $date_to_raw   = sanitize_text_field($_GET['date_to']   ?? '');
    $ts_from  = $date_from_raw ? strtotime($date_from_raw . ' 00:00:00') : strtotime('-7 days midnight');
    $ts_to    = $date_to_raw   ? strtotime($date_to_raw   . ' 23:59:59') : strtotime('today 23:59:59');
    $lbl_from = date_i18n('d/m/Y', $ts_from);
    $lbl_to   = date_i18n('d/m/Y', $ts_to);

    $all_risk    = [];
    $date_rows   = [];
    $table_rows  = [];

    if (function_exists('wc_get_orders')) {
        $raw = wc_get_orders([
            'meta_query' => [['key' => '_whp_ai_verify_result', 'compare' => 'EXISTS']],
            'limit' => 500, 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects',
        ]);
        foreach ($raw as $order) {
            $ai = $order->get_meta('_whp_ai_verify_result');
            if (!is_array($ai) || !in_array($ai['verdict'] ?? '', ['suspicious','invalid'])) continue;

            $risk_pct  = (int) round(floatval($ai['risk_score'] ?? 0) * 100);
            $risk_lvl  = $risk_pct >= 65 ? 'high' : ($risk_pct >= 35 ? 'medium' : 'low');
            $conf_at   = $order->get_meta('_whp_transfer_confirmed_at');
            $conf_ts   = $conf_at ? (int)strtotime($conf_at) : (int)$order->get_date_modified()->getTimestamp();
            $method    = $order->get_payment_method();
            $wmap      = ['MB_WHP_Wallet_MoMo'=>'MoMo','MB_WHP_Wallet_ZaloPay'=>'ZaloPay','MB_WHP_Wallet_VNPAY'=>'VNPAY','MB_WHP_Wallet_ShopeePay'=>'ShopeePay'];
            $pay_label = $wmap[$method] ?? ($method === 'bacs' ? __('CK ngân hàng', 'whp') : $method);
            $bank      = $order->get_meta('_whp_transfer_bank') ?: ($method === 'bacs' ? __('Ngân hàng', 'whp') : $pay_label);
            $phone     = $order->get_billing_phone();

            $row = compact('order','ai','risk_pct','risk_lvl','conf_ts','conf_at','pay_label','bank','phone');
            $all_risk[] = $row;

            if ($conf_ts >= $ts_from && $conf_ts <= $ts_to) {
                $date_rows[] = $row;
                if ($active_filter === 'all' || $risk_lvl === $active_filter) {
                    $table_rows[] = $row;
                }
            }
        }
    }

    // Filter tab counts
    $n_all    = count($date_rows);
    $n_high   = count(array_filter($date_rows, fn($r) => $r['risk_lvl'] === 'high'));
    $n_medium = count(array_filter($date_rows, fn($r) => $r['risk_lvl'] === 'medium'));
    $n_low    = count(array_filter($date_rows, fn($r) => $r['risk_lvl'] === 'low'));

    // ── Donut: verdict_reason → 5 categories (renamed to "Nội dung OK không khớp") ──
    $dcats = ['Nội dung OK không khớp'=>0,'Sai số tiền'=>0,'Ảnh biên lai chỉnh sửa'=>0,'Tài khoản nhận không khớp'=>0,'Khác'=>0];
    foreach ($date_rows as $r) {
        $txt = mb_strtolower(($r['ai']['verdict_reason'] ?? '') . ' ' . implode(' ', (array)($r['ai']['risk_flags'] ?? [])));
        if (strpos($txt,'số tiền')!==false||strpos($txt,'amount')!==false||strpos($txt,'tiền không đúng')!==false) {
            $dcats['Sai số tiền']++;
        } elseif (strpos($txt,'tài khoản')!==false||strpos($txt,'account')!==false||strpos($txt,'stk')!==false) {
            $dcats['Tài khoản nhận không khớp']++;
        } elseif (strpos($txt,'ảnh')!==false||strpos($txt,'chỉnh sửa')!==false||strpos($txt,'photoshop')!==false||strpos($txt,'giả mạo')!==false) {
            $dcats['Ảnh biên lai chỉnh sửa']++;
        } elseif (strpos($txt,'không khớp')!==false||strpos($txt,'không đúng')!==false||strpos($txt,'nội dung')!==false) {
            $dcats['Nội dung OK không khớp']++;
        } else { $dcats['Khác']++; }
    }
    $dtotal = array_sum($dcats);
    if ($dtotal === 0) {
        $dcats = ['Nội dung OK không khớp'=>4,'Sai số tiền'=>3,'Ảnh biên lai chỉnh sửa'=>2,'Tài khoản nhận không khớp'=>1,'Khác'=>1];
        $dtotal = 11;
    }
    $dcols = ['#22c55e','#f97316','#3b82f6','#eab308','#94a3b8'];
    $dnames = array_keys($dcats); $dvals = array_values($dcats);
    $conic = ''; $acc = 0;
    foreach ($dvals as $i => $v) {
        $deg = round($v/$dtotal*360);
        $conic .= $dcols[$i]." {$acc}deg ".($acc+$deg)."deg, ";
        $acc += $deg;
    }
    $conic = rtrim($conic, ', ');

    // ── Sparkline: hourly counts today ────────────────────────────────
    $today_start = strtotime('today midnight');
    $hourly = array_fill(0, 24, 0);
    foreach ($all_risk as $r) {
        if ($r['conf_ts'] >= $today_start) $hourly[(int)date('G',$r['conf_ts'])]++;
    }
    $today_total = array_sum($hourly);
    $sp_w = 200; $sp_h = 48; $max_h = max(1, max($hourly));
    $pts = [];
    for ($h = 0; $h <= 23; $h++) {
        $pts[] = round($h/23*$sp_w) . ',' . round($sp_h - ($hourly[$h]/$max_h)*($sp_h-6)-3);
    }
    $polyline = implode(' ', $pts);
    $area_pts = "0,$sp_h " . $polyline . " $sp_w,$sp_h";

    $base_url  = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=risk');
    $queue_url = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=queue');
    $nonce     = wp_create_nonce('wpaap_generate_nonce');

    // Date input values for filter popup (fallback to displayed range)
    $date_from_input = $date_from_raw ?: date('Y-m-d', $ts_from);
    $date_to_input   = $date_to_raw   ?: date('Y-m-d', $ts_to);
    ?>
<style>
/* ═══ RISK POPUP ═════════════════════════════════════════════════════ */
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
/* Meta row */
.rskp-meta{display:grid;grid-template-columns:repeat(4,1fr);border-bottom:1px solid #f1f5f9;flex-shrink:0;}
.rskp-meta-item{padding:10px 16px;border-right:1px solid #f1f5f9;}
.rskp-meta-item:last-child{border-right:none;}
.rskp-meta-lbl{font-size:10.5px;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-bottom:3px;}
.rskp-meta-val{font-size:12.5px;font-weight:600;color:#0f172a;}
.rskp-meta-val.amount{color:#059669;}
/* Body */
.rskp-body{display:grid;grid-template-columns:1fr 220px;overflow:hidden;flex:1;min-height:0;}
.rskp-main{display:grid;grid-template-columns:1fr 1fr;border-right:1px solid #f1f5f9;overflow-y:auto;}
.rskp-receipt-panel{padding:16px;border-right:1px solid #f1f5f9;}
.rskp-ai-panel{padding:16px;overflow-y:auto;}
.rskp-panel-hd{font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;padding-bottom:7px;border-bottom:1px solid #f1f5f9;}
/* Receipt */
.rskp-receipt-img{width:100%;max-height:240px;object-fit:contain;border-radius:10px;border:1px solid #e2e8f0;background:#f8fafc;display:block;cursor:zoom-in;}
.rskp-receipt-placeholder{min-height:120px;background:#f8fafc;border-radius:10px;border:2px dashed #e2e8f0;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;gap:7px;text-align:center;}
.rskp-receipt-actions{display:flex;gap:6px;margin-top:8px;}
.rskp-receipt-btn{flex:1;padding:6px 4px;border:1px solid #e2e8f0;border-radius:7px;background:#fff;font-size:11px;color:#475569;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:4px;transition:background .1s;}
.rskp-receipt-btn:hover{background:#f1f5f9;color:#0f172a;}
.rskp-btn-verify{width:100%;margin-top:10px;padding:10px;border:none;border-radius:9px;cursor:pointer;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;gap:7px;background:linear-gradient(135deg,#e11d48,#be123c);color:#fff;box-shadow:0 3px 12px rgba(225,29,72,.2);transition:opacity .15s;}
.rskp-btn-verify:hover{opacity:.9;}
.rskp-btn-verify:disabled{opacity:.5;cursor:not-allowed;}
/* AI checklist */
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
/* Sidebar */
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
/* Spinner */
@keyframes rskp-spin{to{transform:rotate(360deg);}}
.rskp-spinner{animation:rskp-spin .8s linear infinite;}
/* Lightbox */
.rskp-lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999999;align-items:center;justify-content:center;}
.rskp-lightbox.open{display:flex;}
.rskp-lightbox img{max-width:90vw;max-height:90vh;object-fit:contain;border-radius:10px;}
.rskp-lightbox-close{position:absolute;top:16px;right:20px;color:#fff;font-size:32px;cursor:pointer;background:none;border:none;line-height:1;}

/* ═══ RISK REDESIGN v3 (rsk3-) ══════════════════════════════════════ */
.rsk3-outer{display:grid;grid-template-columns:1fr 260px;gap:18px;align-items:start;}
@media(max-width:960px){.rsk3-outer{grid-template-columns:1fr;}}

/* ── Filter bar ── */
.rsk3-bar{display:flex;align-items:center;gap:8px;margin-bottom:14px;flex-wrap:wrap;}
.rsk3-tabs{display:flex;align-items:center;gap:6px;flex:1;flex-wrap:wrap;}
.rsk3-tab{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:20px;font-size:12.5px;font-weight:600;text-decoration:none;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;white-space:nowrap;transition:all .15s;cursor:pointer;}
.rsk3-tab:hover{background:#f8fafc;border-color:#cbd5e1;}
.rsk3-tab.active{background:#1e293b;border-color:#1e293b;color:#fff;}
.rsk3-tab.high{color:#ef4444;border-color:#fca5a5;}
.rsk3-tab.high:hover{background:#fef2f2;}
.rsk3-tab.medium{color:#f97316;border-color:#fed7aa;}
.rsk3-tab.medium:hover{background:#fff7ed;}
.rsk3-tab.low{color:#22c55e;border-color:#86efac;}
.rsk3-tab.low:hover{background:#f0fdf4;}
.rsk3-tab.active.high{background:#ef4444;border-color:#ef4444;color:#fff;}
.rsk3-tab.active.medium{background:#f97316;border-color:#f97316;color:#fff;}
.rsk3-tab.active.low{background:#22c55e;border-color:#22c55e;color:#fff;}
.rsk3-tab-cnt{font-size:11px;font-weight:700;background:rgba(255,255,255,.25);padding:1px 6px;border-radius:10px;}
.rsk3-tab:not(.active) .rsk3-tab-cnt{background:rgba(0,0,0,.07);color:inherit;}

/* ── Bar tools ── */
.rsk3-bar-tools{display:flex;align-items:center;gap:6px;position:relative;}
.rsk3-date-badge{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:12px;font-weight:500;color:#475569;background:#fff;border:1.5px solid #e2e8f0;white-space:nowrap;}
.rsk3-btn-filter{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;color:#475569;font-size:12px;font-weight:600;cursor:pointer;transition:all .14s;white-space:nowrap;}
.rsk3-btn-filter:hover{border-color:#cbd5e1;background:#f8fafc;color:#0f172a;}
.rsk3-btn-filter.active{border-color:#6366f1;background:#eef2ff;color:#4f46e5;}
.rsk3-btn-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;transition:all .14s;}
.rsk3-btn-icon:hover{border-color:#cbd5e1;background:#f8fafc;color:#0f172a;}

/* ── Filter popup ── */
.rsk3-filter-popup{display:none;position:absolute;top:calc(100% + 6px);right:0;z-index:99990;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 6px 24px rgba(0,0,0,.10);padding:14px;min-width:260px;}
.rsk3-filter-popup.open{display:block;}
.rsk3-filter-popup label{display:block;font-size:11.5px;font-weight:600;color:#374151;margin-bottom:4px;}
.rsk3-filter-popup input[type="date"]{width:100%;padding:6px 9px;border:1.5px solid #e2e8f0;border-radius:7px;font-size:12px;color:#1e293b;background:#fafafa;outline:none;transition:border-color .13s;box-sizing:border-box;}
.rsk3-filter-popup input[type="date"]:focus{border-color:#6366f1;background:#fff;}
.rsk3-fp-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;}
.rsk3-fp-apply{display:block;width:100%;padding:7px;border-radius:8px;border:none;background:#1e293b;color:#fff;font-size:12px;font-weight:700;cursor:pointer;transition:background .13s;}
.rsk3-fp-apply:hover{background:#334155;}

/* ── Section title ── */
.rsk3-section-title{font-size:13.5px;font-weight:700;color:#0f172a;margin-bottom:10px;}

/* ── Table ── */
.rsk3-table-wrap{background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04);}
.rsk3-thead{display:grid;grid-template-columns:44px 140px 100px 110px 130px 90px 100px 110px 70px;gap:0;padding:10px 14px;background:#fafafa;border-bottom:1px solid #e2e8f0;font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;align-items:center;}
.rsk3-row{display:grid;grid-template-columns:44px 140px 100px 110px 130px 90px 100px 110px 70px;gap:0;padding:11px 14px;border-bottom:1px solid #f1f5f9;align-items:center;transition:background .1s;position:relative;}
.rsk3-row:last-child{border-bottom:none;}
.rsk3-row:hover{background:#fafafa;}
.rsk3-row.high  {border-left:3px solid #ef4444;}
.rsk3-row.medium{border-left:3px solid #f97316;}
.rsk3-row.low   {border-left:3px solid #22c55e;}

/* ── Status circle ── */
.rsk3-circle{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.rsk3-circle.high  {background:#fef2f2;border:2px solid #fca5a5;}
.rsk3-circle.medium{background:#fff7ed;border:2px solid #fed7aa;}
.rsk3-circle.low   {background:#f0fdf4;border:2px solid #86efac;}

/* ── Order cell (order# + name stacked) ── */
.rsk3-oid{font-size:12.5px;font-weight:700;color:#059669;}
.rsk3-oname{font-size:11.5px;font-weight:600;color:#334155;margin-top:2px;}

/* ── Phone cell ── */
.rsk3-phone{font-size:12px;color:#64748b;}

/* ── Amount ── */
.rsk3-amount{font-size:12.5px;font-weight:700;color:#0f172a;white-space:nowrap;}

/* ── Date ── */
.rsk3-date{font-size:11.5px;color:#64748b;}

/* ── Bank / method ── */
.rsk3-bank,.rsk3-pay{font-size:12px;color:#334155;}

/* ── Risk cell ── */
.rsk3-risk-num{font-size:13px;font-weight:700;}
.rsk3-risk-num.high  {color:#ef4444;}
.rsk3-risk-num.medium{color:#f97316;}
.rsk3-risk-num.low   {color:#22c55e;}
.rsk3-risk-dot{display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:3px;vertical-align:middle;}
.rsk3-risk-dot.high  {background:#ef4444;}
.rsk3-risk-dot.medium{background:#f97316;}
.rsk3-risk-dot.low   {background:#22c55e;}
.rsk3-risk-label{font-size:10.5px;color:#94a3b8;margin-top:1px;}
.rsk3-risk-label.high{color:#ef4444;}
.rsk3-risk-label.medium{color:#f97316;}
.rsk3-risk-label.low{color:#22c55e;}

/* ── Action buttons ── */
.rsk3-btn-view{display:inline-flex;align-items:center;justify-content:center;width:27px;height:27px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;text-decoration:none;transition:all .13s;}
.rsk3-btn-view:hover{border-color:#bfdbfe;background:#eff6ff;color:#2563eb;}
.rsk3-btn-more{display:inline-flex;align-items:center;justify-content:center;width:27px;height:27px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;color:#64748b;cursor:pointer;transition:all .13s;}
.rsk3-btn-more:hover{border-color:#cbd5e1;background:#f8fafc;color:#0f172a;}

/* ── Dropdown ── */
.rsk3-dd{display:none;position:fixed;z-index:99999;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 4px 18px rgba(0,0,0,.10);padding:4px;min-width:168px;}
.rsk3-dd-item{display:flex;align-items:center;gap:7px;padding:7px 10px;border-radius:7px;font-size:12.5px;color:#374151;cursor:pointer;transition:background .12s;text-decoration:none;border:none;background:none;width:100%;text-align:left;}
.rsk3-dd-item:hover{background:#f8fafc;}
.rsk3-dd-sep{height:1px;background:#f1f5f9;margin:3px 0;}
@keyframes rsk3-spin{to{transform:rotate(360deg);}}

/* ── Empty state ── */
.rsk3-empty{text-align:center;padding:48px 20px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;}
.rsk3-empty h3{font-size:15px;font-weight:700;color:#0f172a;margin:10px 0 5px;}
.rsk3-empty p{font-size:12.5px;color:#64748b;margin:0;}

/* ── Pagination ── */
.rsk3-pag{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-top:1px solid #f1f5f9;font-size:12px;color:#64748b;}
.rsk3-pag-btns{display:flex;gap:4px;}
.rsk3-pag-btn{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;border-radius:7px;border:1.5px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;padding:0 6px;transition:all .13s;}
.rsk3-pag-btn:hover:not(:disabled){border-color:#cbd5e1;background:#f8fafc;}
.rsk3-pag-btn.active{background:#1e293b;border-color:#1e293b;color:#fff;}
.rsk3-pag-btn:disabled{opacity:.4;cursor:default;}

/* ── Sidebar ── */
.rsk3-sb{display:flex;flex-direction:column;gap:12px;}
.rsk3-scard{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.04);}
.rsk3-scard-title{font-size:13px;font-weight:700;color:#0f172a;margin-bottom:12px;}

/* ── Donut ── */
.rsk3-donut-wrap{display:flex;align-items:center;gap:14px;}
.rsk3-donut{width:88px;height:88px;border-radius:50%;flex-shrink:0;position:relative;}
.rsk3-donut-hole{position:absolute;width:56px;height:56px;border-radius:50%;background:#fff;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;}
.rsk3-donut-num{font-size:17px;font-weight:800;color:#0f172a;line-height:1;}
.rsk3-donut-lbl{font-size:8.5px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;}
.rsk3-donut-legend{flex:1;display:flex;flex-direction:column;gap:5px;}
.rsk3-dleg-row{display:flex;align-items:center;gap:6px;}
.rsk3-dleg-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.rsk3-dleg-name{flex:1;font-size:10.5px;color:#475569;line-height:1.3;}
.rsk3-dleg-pct{font-size:10.5px;font-weight:700;color:#0f172a;white-space:nowrap;}
.rsk3-report-btn{display:flex;align-items:center;justify-content:center;gap:5px;margin-top:12px;padding:7px 10px;border-radius:8px;border:1.5px solid #e2e8f0;background:#fff;color:#475569;font-size:12px;font-weight:600;text-decoration:none;transition:all .14s;cursor:pointer;}
.rsk3-report-btn:hover{border-color:#cbd5e1;background:#f8fafc;color:#0f172a;}

/* ── Sparkline card ── */
.rsk3-sp-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.rsk3-sp-title{font-size:13px;font-weight:700;color:#0f172a;}
.rsk3-sp-today-badge{font-size:11px;padding:3px 9px;border-radius:20px;background:#f1f5f9;color:#64748b;font-weight:600;}
.rsk3-sp-svg{width:100%;overflow:visible;}
.rsk3-sp-axis{display:flex;justify-content:space-between;font-size:10px;color:#94a3b8;margin-top:4px;}

/* ── Date range picker (queue style) ── */
.aipq-daterange-wrap{position:relative;}
.aipq-daterange{display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;color:#374151;cursor:pointer;white-space:nowrap;transition:border-color .15s,color .15s;}
.aipq-daterange:hover,.aipq-daterange.open{border-color:#e11d48;color:#e11d48;}
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
</style>

<?php
$verify_base = admin_url('admin.php?page=mb-wphelper-ai&subtab=ai-payment&aipay_tab=verify');
$risk_labels = ['high'=>__('Rủi ro cao', 'whp'),'medium'=>__('Rủi ro trung bình', 'whp'),'low'=>__('Rủi ro thấp', 'whp')];
// Build filter form action URL (strips date params, keeps page/tab)
$filter_action = add_query_arg([
    'page'      => 'mb-wphelper-ai',
    'subtab'    => 'ai-payment',
    'aipay_tab' => 'risk',
], admin_url('admin.php'));
?>

<div class="rsk3-outer">
<!-- ════ LEFT: filter + table ════════════════════════════════════════ -->
<div>
    <!-- Filter bar -->
    <div class="rsk3-bar">
        <div class="rsk3-tabs">
            <a href="<?php echo esc_url(add_query_arg(['risk_lvl'=>false,'date_from'=>$date_from_raw?:false,'date_to'=>$date_to_raw?:false],$base_url)); ?>"
               class="rsk3-tab <?php echo $active_filter==='all'?'active':''; ?>">
                <?php esc_html_e('Tất cả', 'whp'); ?> <span class="rsk3-tab-cnt"><?php echo $n_all; ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg(['risk_lvl'=>'high','date_from'=>$date_from_raw?:false,'date_to'=>$date_to_raw?:false],$base_url)); ?>"
               class="rsk3-tab high <?php echo $active_filter==='high'?'active':''; ?>">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <?php esc_html_e('Rủi ro cao', 'whp'); ?> <span class="rsk3-tab-cnt"><?php echo $n_high; ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg(['risk_lvl'=>'medium','date_from'=>$date_from_raw?:false,'date_to'=>$date_to_raw?:false],$base_url)); ?>"
               class="rsk3-tab medium <?php echo $active_filter==='medium'?'active':''; ?>">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="8" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="13" y2="14"/></svg>
                <?php esc_html_e('Rủi ro trung bình', 'whp'); ?> <span class="rsk3-tab-cnt"><?php echo $n_medium; ?></span>
            </a>
            <a href="<?php echo esc_url(add_query_arg(['risk_lvl'=>'low','date_from'=>$date_from_raw?:false,'date_to'=>$date_to_raw?:false],$base_url)); ?>"
               class="rsk3-tab low <?php echo $active_filter==='low'?'active':''; ?>">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <?php esc_html_e('Rủi ro thấp', 'whp'); ?> <span class="rsk3-tab-cnt"><?php echo $n_low; ?></span>
            </a>
        </div>
        <div class="rsk3-bar-tools">
            <!-- Date range picker (same style as queue tab) -->
            <form method="get" action="<?php echo esc_url($filter_action); ?>" id="rsk3-filter-form" style="display:contents">
                <input type="hidden" name="page"      value="mb-wphelper-ai">
                <input type="hidden" name="subtab"    value="ai-payment">
                <input type="hidden" name="aipay_tab" value="risk">
                <?php if ($active_filter !== 'all'): ?>
                <input type="hidden" name="risk_lvl"  value="<?php echo esc_attr($active_filter); ?>">
                <?php endif; ?>
                <div class="aipq-daterange-wrap">
                    <div class="aipq-daterange<?php echo ($date_from_raw || $date_to_raw) ? ' open' : ''; ?>" id="rsk3-daterange-trigger">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span id="rsk3-date-label"><?php echo esc_html($lbl_from . ' – ' . $lbl_to); ?></span>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="aipq-date-popover" id="rsk3-date-popover">
                        <div class="aipq-date-row">
                            <label><?php esc_html_e('Từ ngày', 'whp'); ?></label>
                            <input type="date" name="date_from" id="rsk3-inp-from"
                                   value="<?php echo esc_attr($date_from_input); ?>">
                        </div>
                        <div class="aipq-date-row">
                            <label><?php esc_html_e('Đến ngày', 'whp'); ?></label>
                            <input type="date" name="date_to" id="rsk3-inp-to"
                                   value="<?php echo esc_attr($date_to_input); ?>">
                        </div>
                        <div class="aipq-date-actions">
                            <button type="button" class="aipq-date-clear" id="rsk3-date-clear"><?php esc_html_e('Xóa', 'whp'); ?></button>
                            <button type="submit" class="aipq-date-apply"><?php esc_html_e('Áp dụng', 'whp'); ?></button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Refresh button -->
            <button class="rsk3-btn-icon" type="button" title="<?php esc_attr_e('Làm mới', 'whp'); ?>" onclick="location.reload()" aria-label="<?php esc_attr_e('Làm mới', 'whp'); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            </button>
        </div>
    </div>

<?php if (empty($table_rows)): ?>
    <div class="rsk3-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <h3><?php esc_html_e('Không có giao dịch rủi ro', 'whp'); ?></h3>
        <p><?php esc_html_e('AI chưa phát hiện giao dịch nghi ngờ trong khoảng thời gian này.', 'whp'); ?></p>
    </div>
    <?php else: ?>
    <div class="rsk3-table-wrap">
        <!-- Table header -->
        <div class="rsk3-thead">
            <span></span>
            <span><?php esc_html_e('Đơn hàng', 'whp'); ?></span>
            <span><?php esc_html_e('Khách hàng', 'whp'); ?></span>
            <span><?php esc_html_e('Số tiền', 'whp'); ?></span>
            <span><?php esc_html_e('Thời gian CK', 'whp'); ?></span>
            <span><?php esc_html_e('Ngân hàng', 'whp'); ?></span>
            <span><?php esc_html_e('Phương thức', 'whp'); ?></span>
            <span><?php esc_html_e('Rủi ro', 'whp'); ?></span>
            <span><?php esc_html_e('Thao tác', 'whp'); ?></span>
        </div>

        <!-- Table rows -->
        <div id="rsk3-tbody">
        <?php foreach ($table_rows as $item):
            $order   = $item['order'];
            $oid     = $order->get_id();
            $name    = trim($order->get_billing_first_name().' '.$order->get_billing_last_name()) ?: __('Khách', 'whp');
            $total   = number_format(floatval($order->get_total()), 0, ',', '.') . ' VND';
            $conf_at = $item['conf_at'] ?: '';
            $date_f  = $conf_at ? date_i18n('d/m/Y H:i', strtotime($conf_at)) : date_i18n('d/m/Y H:i', $item['conf_ts']);
            $lvl     = $item['risk_lvl'];
            $pct     = $item['risk_pct'];
            $detail_url = esc_url(add_query_arg('order_id', $oid, $verify_base));

            // Circle icon: low = green checkmark, medium = orange circle-exclaim, high = red warning
            if ($lvl === 'low') {
                $circle_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
            } elseif ($lvl === 'medium') {
                $circle_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
            } else {
                $circle_icon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg>';
            }
        ?>
        <div class="rsk3-row <?php echo esc_attr($lvl); ?>"
             data-order-id="<?php echo esc_attr($oid); ?>"
             data-risk="<?php echo esc_attr($lvl); ?>">
            <!-- Col 1: Status circle -->
            <div><div class="rsk3-circle <?php echo esc_attr($lvl); ?>"><?php echo $circle_icon; ?></div></div>

            <!-- Col 2: Order# + Customer name stacked -->
            <div>
                <div class="rsk3-oid">#<?php echo esc_html($oid); ?></div>
                <div class="rsk3-oname"><?php echo esc_html($name); ?></div>
            </div>

            <!-- Col 3: Phone -->
            <div class="rsk3-phone"><?php echo $item['phone'] ? esc_html($item['phone']) : '—'; ?></div>

            <!-- Col 4: Amount -->
            <div class="rsk3-amount"><?php echo esc_html($total); ?></div>

            <!-- Col 5: Transfer datetime -->
            <div class="rsk3-date"><?php echo esc_html($date_f); ?></div>

            <!-- Col 6: Bank -->
            <div class="rsk3-bank"><?php echo esc_html($item['bank']); ?></div>

            <!-- Col 7: Payment method -->
            <div class="rsk3-pay"><?php echo esc_html($item['pay_label']); ?></div>

            <!-- Col 8: Risk score + label -->
            <div>
                <div class="rsk3-risk-num <?php echo esc_attr($lvl); ?>">
                    <span class="rsk3-risk-dot <?php echo esc_attr($lvl); ?>"></span><?php echo $pct; ?>%
                </div>
                <div class="rsk3-risk-label <?php echo esc_attr($lvl); ?>"><?php echo esc_html($risk_labels[$lvl]); ?></div>
            </div>

            <!-- Col 9: Actions (view + more) -->
            <div style="display:flex;align-items:center;gap:5px;">
                <a href="<?php echo $detail_url; ?>"
                   class="rsk3-btn-view"
                   title="<?php esc_attr_e('Xem chi tiết', 'whp'); ?>"
                   aria-label="<?php echo esc_attr(sprintf(__( 'Xem chi tiết đơn #%s', 'whp'), $oid)); ?>">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <button class="rsk3-btn-more rsk3-dd-trigger"
                        data-oid="<?php echo esc_attr($oid); ?>"
                        data-url="<?php echo $detail_url; ?>"
                        title="<?php esc_attr_e('Thao tác', 'whp'); ?>"
                        aria-label="<?php echo esc_attr(sprintf(__( 'Thao tác cho đơn #%s', 'whp'), $oid)); ?>">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        </div><!-- #rsk3-tbody -->

        <!-- Pagination -->
        <div class="rsk3-pag" id="rsk3-pag">
            <span id="rsk3-pag-info"></span>
            <div class="rsk3-pag-btns" id="rsk3-pag-btns"></div>
        </div>
    </div><!-- .rsk3-table-wrap -->
    <?php endif; ?>
</div><!-- left -->

<!-- ════ RIGHT: sidebar — 2 cards only ══════════════════════════════ -->
<div class="rsk3-sb">

    <!-- Card 1: Nguyên nhân rủi ro (donut) -->
    <div class="rsk3-scard">
        <div class="rsk3-scard-title"><?php esc_html_e('Nguyên nhân rủi ro', 'whp'); ?></div>
        <div class="rsk3-donut-wrap">
            <div class="rsk3-donut" style="background:conic-gradient(<?php echo $conic; ?>)">
                <div class="rsk3-donut-hole">
                    <span class="rsk3-donut-num"><?php echo $n_all; ?></span>
                    <span class="rsk3-donut-lbl"><?php esc_html_e('đơn', 'whp'); ?></span>
                </div>
            </div>
            <div class="rsk3-donut-legend">
                <?php foreach (array_keys($dcats) as $i => $cat):
                    $pct_d = $dtotal > 0 ? round($dcats[$cat]/$dtotal*100) : 0;
                ?>
                <div class="rsk3-dleg-row">
                    <div class="rsk3-dleg-dot" style="background:<?php echo $dcols[$i]; ?>"></div>
                    <span class="rsk3-dleg-name"><?php echo esc_html($cat); ?></span>
                    <span class="rsk3-dleg-pct"><?php echo $pct_d; ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <a href="<?php echo esc_url(add_query_arg(['subtab'=>'ai-payment','aipay_tab'=>'logs'], admin_url('admin.php?page=mb-wphelper-ai'))); ?>"
           class="rsk3-report-btn">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
            <?php esc_html_e('Xem báo cáo chi tiết', 'whp'); ?>
        </a>
    </div>

    <!-- Card 2: Thống kê rủi ro (sparkline) -->
    <div class="rsk3-scard">
        <div class="rsk3-sp-header">
            <span class="rsk3-sp-title"><?php esc_html_e('Thống kê rủi ro', 'whp'); ?></span>
            <span class="rsk3-sp-today-badge"><?php esc_html_e('Hôm nay', 'whp'); ?></span>
        </div>
        <svg class="rsk3-sp-svg"
             viewBox="0 0 <?php echo $sp_w; ?> <?php echo $sp_h; ?>"
             height="<?php echo $sp_h; ?>px"
             preserveAspectRatio="none"
             role="img"
             aria-label="<?php echo esc_attr(sprintf(__( 'Biểu đồ rủi ro hôm nay: %s đơn', 'whp'), $today_total)); ?>">
            <defs>
                <linearGradient id="rsk3-sp-grad" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#ef4444" stop-opacity="0.25"/>
                    <stop offset="100%" stop-color="#ef4444" stop-opacity="0.02"/>
                </linearGradient>
            </defs>
            <polygon points="<?php echo esc_attr($area_pts); ?>" fill="url(#rsk3-sp-grad)"/>
            <polyline points="<?php echo esc_attr($polyline); ?>" fill="none" stroke="#ef4444" stroke-width="1.8" stroke-linejoin="round" stroke-linecap="round"/>
            <!-- Count annotation top-right -->
            <text x="<?php echo $sp_w - 2; ?>" y="10" text-anchor="end" font-size="10" font-weight="700" fill="#ef4444" font-family="sans-serif"><?php echo $today_total; ?> <?php esc_html_e('đơn', 'whp'); ?></text>
        </svg>
        <div class="rsk3-sp-axis">
            <span>00:00</span><span>06:00</span><span>12:00</span><span>18:00</span><span>24:00</span>
        </div>
    </div>

</div><!-- sidebar -->
</div><!-- outer -->

<!-- Shared action dropdown -->
<div class="rsk3-dd" id="rsk3-dd" role="menu">
    <button class="rsk3-dd-item" id="rsk3-dd-view" role="menuitem">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        <?php esc_html_e('Xem chi tiết', 'whp'); ?>
    </button>
    <button class="rsk3-dd-item" id="rsk3-dd-ai" role="menuitem" style="color:#059669">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
        <?php esc_html_e('Xác minh AI', 'whp'); ?>
    </button>
    <div class="rsk3-dd-sep"></div>
    <button class="rsk3-dd-item" id="rsk3-dd-ok" role="menuitem" style="color:#059669">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        <?php esc_html_e('Xác nhận thanh toán', 'whp'); ?>
    </button>
    <button class="rsk3-dd-item" id="rsk3-dd-no" role="menuitem" style="color:#dc2626">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        <?php esc_html_e('Từ chối đơn', 'whp'); ?>
    </button>
</div>

<script>
var whpI18n = <?php echo wp_json_encode([
    'showingTransactions'   => __( 'Hiện thị {range} / {total} giao dịch', 'whp' ),
    'verifying'             => __( 'Đang xác minh…', 'whp' ),
    'verifySuccess2'        => __( 'Xác minh thành công!', 'whp' ),
    'cannotVerifyNoReceipt' => __( 'Không thể xác minh. Đơn hàng có thể chưa có biên lai.', 'whp' ),
    'connectionFailed'      => __( 'Kết nối thất bại.', 'whp' ),
    'processing'            => __( 'Đang xử lý…', 'whp' ),
    'paymentConfirmed'      => __( 'Đã xác nhận thanh toán thành công!', 'whp' ),
    'orderRejected'         => __( 'Đã từ chối đơn hàng.', 'whp' ),
    'markedSuspicious'      => __( 'Đã đánh dấu nghi ngờ.', 'whp' ),
    'errorOccurred'         => __( 'Có lỗi xảy ra.', 'whp' ),
    'connectionFailedRetry' => __( 'Kết nối thất bại. Thử lại.', 'whp' ),
    'confirmPaymentOrder'   => __( 'Xác nhận thanh toán đơn', 'whp' ),
    'rejectOrder'           => __( 'Từ chối đơn', 'whp' ),
    'riskHigh'              => __( 'Rủi ro cao', 'whp' ),
    'riskMedium'            => __( 'Rủi ro trung bình', 'whp' ),
    'riskLow'               => __( 'Rủi ro thấp', 'whp' ),
    'noReceiptImage'        => __( 'Chưa có ảnh biên lai', 'whp' ),
    'ocrConfidence'         => __( 'Độ tin cậy OCR', 'whp' ),
    'verdictValid'          => __( '✓ Biên lai hợp lệ', 'whp' ),
    'verdictSuspicious'     => __( '⚠ Nghi ngờ gian lận', 'whp' ),
    'verdictInvalid'        => __( '✗ Biên lai không hợp lệ', 'whp' ),
    'noAnalysisResult'      => __( 'Chưa có kết quả phân tích', 'whp' ),
    'orderId'               => __( 'Mã đơn', 'whp' ),
    'customer'              => __( 'Khách hàng', 'whp' ),
    'phone'                 => __( 'SĐT', 'whp' ),
    'orderValue'            => __( 'Giá trị đơn', 'whp' ),
    'status'                => __( 'Trạng thái', 'whp' ),
    'verifyingAI'           => __( 'Đang xác minh…', 'whp' ),
    'loadDataFailed'        => __( 'Không tải được dữ liệu.', 'whp' ),
    'confirmPayment'        => __( 'Xác nhận thanh toán', 'whp' ),
    'markSuspicious'        => __( 'Đánh dấu nghi ngờ', 'whp' ),
    'rejectPayment'         => __( 'Từ chối thanh toán', 'whp' ),
    'requestReceipt'        => __( 'Yêu cầu gửi lại biên lai', 'whp' ),
    'paymentConfirmed2'     => __( 'Đã xác nhận thanh toán!', 'whp' ),
    'paymentRejected'       => __( 'Đã từ chối thanh toán.', 'whp' ),
    'receiptRequestSent'    => __( 'Đã gửi yêu cầu biên lai tới khách!', 'whp' ),
    'actionSuccess'         => __( 'Thao tác thành công!', 'whp' ),
    'noteSaved'             => __( 'Đã lưu ghi chú.', 'whp' ),
]); ?>;
(function(){
'use strict';

// ── Pagination ────────────────────────────────────────────────────
var rows    = Array.from(document.querySelectorAll('#rsk3-tbody .rsk3-row'));
var perPage = 6, curPage = 1;
var nonce   = <?php echo wp_json_encode($nonce); ?>;

function render(){
    var total = rows.length;
    var pages = Math.max(1, Math.ceil(total / perPage));
    curPage   = Math.min(curPage, pages);
    rows.forEach(function(r){ r.style.display = 'none'; });
    rows.slice((curPage - 1) * perPage, curPage * perPage).forEach(function(r){ r.style.display = ''; });
    var info = document.getElementById('rsk3-pag-info');
    var start = (curPage - 1) * perPage + 1;
    var end   = Math.min(curPage * perPage, total);
    if (info) info.textContent = whpI18n.showingTransactions.replace('{range}', total > 0 ? start+'-'+end : '0').replace('{total}', total);
    buildPag(pages);
}

function buildPag(pages){
    var wrap = document.getElementById('rsk3-pag-btns');
    if (!wrap) return;
    wrap.innerHTML = '';

    function mkBtn(lbl, page, dis, act){
        var b = document.createElement('button');
        b.className = 'rsk3-pag-btn' + (act ? ' active' : '');
        b.textContent = lbl;
        b.disabled = dis;
        if (!dis) b.addEventListener('click', function(){ curPage = page; render(); });
        return b;
    }

    wrap.appendChild(mkBtn('‹', curPage - 1, curPage === 1, false));
    var s = Math.max(1, curPage - 2), e = Math.min(pages, s + 4);
    for (var p = s; p <= e; p++) wrap.appendChild(mkBtn(String(p), p, false, p === curPage));
    wrap.appendChild(mkBtn('›', curPage + 1, curPage === pages, false));
}

if (rows.length) render();

// ── Date range popover (queue style) ─────────────────────────────
(function(){
    var trigger = document.getElementById('rsk3-daterange-trigger');
    var popover = document.getElementById('rsk3-date-popover');
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
    var clearBtn = document.getElementById('rsk3-date-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function(){
            document.getElementById('rsk3-inp-from').value = '';
            document.getElementById('rsk3-inp-to').value = '';
            document.getElementById('rsk3-filter-form').submit();
        });
    }
    var inpFrom = document.getElementById('rsk3-inp-from');
    var inpTo   = document.getElementById('rsk3-inp-to');
    var label   = document.getElementById('rsk3-date-label');
    function updateLabel(){
        var f = inpFrom ? inpFrom.value : '', t = inpTo ? inpTo.value : '';
        if (!f && !t) return;
        function fmt(d){ if(!d) return ''; var p=d.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }
        label.textContent = (f ? fmt(f) : '…') + ' – ' + (t ? fmt(t) : '…');
    }
    if (inpFrom) inpFrom.addEventListener('change', updateLabel);
    if (inpTo)   inpTo.addEventListener('change', updateLabel);
})();

// ── Dropdown (row actions) ────────────────────────────────────────
var dd = document.getElementById('rsk3-dd'), ddOid = null, ddUrl = null;

document.querySelectorAll('.rsk3-dd-trigger').forEach(function(btn){
    btn.addEventListener('click', function(e){
        e.stopPropagation();
        ddOid = btn.dataset.oid;
        ddUrl = btn.dataset.url;
        var r = btn.getBoundingClientRect();
        var menuH = 160;
        var top = (window.innerHeight - r.bottom < menuH) ? (r.top - menuH - 4) : (r.bottom + 4);
        dd.style.top  = top + 'px';
        dd.style.left = (r.right - 170) + 'px';
        dd.style.display = 'block';
    });
});

document.addEventListener('click', function(){
    if (dd) dd.style.display = 'none';
});
if (dd) dd.addEventListener('click', function(e){ e.stopPropagation(); });

// rsk3-dd-view → handled by popup listener below

document.getElementById('rsk3-dd-ai')?.addEventListener('click', function(){
    if (!ddOid) return;
    dd.style.display = 'none';
    var btn = document.querySelector('.rsk3-dd-trigger[data-oid="' + ddOid + '"]');
    var orig = btn ? btn.innerHTML : '';
    if (btn) btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation:rsk3-spin 1s linear infinite"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
    wpaapToast(whpI18n.verifying, 'info');
    jQuery.post(ajaxurl, {action:'wpaap_aipay_verify', nonce:nonce, order_id:ddOid}, function(res){
        if (btn) btn.innerHTML = orig;
        if (res && res.success) {
            wpaapToast(whpI18n.verifySuccess2, 'success');
            setTimeout(function(){ location.reload(); }, 1500);
        } else {
            wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.cannotVerifyNoReceipt, 'error');
        }
    }).fail(function(){ if (btn) btn.innerHTML = orig; wpaapToast(whpI18n.connectionFailed, 'error'); });
});

function doAction(type, msg){
    if (!ddOid) return;
    dd.style.display = 'none';
    var _oid2 = ddOid;
    wpaapConfirm(msg + ' #' + _oid2 + '?', function() {
        wpaapToast(whpI18n.processing, 'info');
        var successMsgs2 = {confirm: whpI18n.paymentConfirmed, reject: whpI18n.orderRejected, suspect: whpI18n.markedSuspicious};
        jQuery.post(ajaxurl, {action:'wpaap_aipay_order_action', nonce:nonce, order_id:_oid2, type:type}, function(res){
            if (res && res.success) {
                wpaapToast(successMsgs2[type] || whpI18n.actionSuccess, 'success');
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.errorOccurred, 'error');
            }
        }).fail(function(){
            wpaapToast(whpI18n.connectionFailedRetry, 'error');
        });
    });
    return;
}

document.getElementById('rsk3-dd-ok')?.addEventListener('click', function(){ doAction('confirm', whpI18n.confirmPaymentOrder); });
document.getElementById('rsk3-dd-no')?.addEventListener('click', function(){ doAction('reject', whpI18n.rejectOrder); });

// ── Risk detail popup ─────────────────────────────────────────────
var rskpNonce  = <?php echo wp_json_encode($nonce); ?>;
var rskpActive = null; // current order id in popup

function rskpOpen(orderId) {
    rskpActive = orderId;
    var overlay = document.getElementById('rskp-overlay');
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    rskpLoadDetail(orderId);
}
function rskpClose() {
    var overlay = document.getElementById('rskp-overlay');
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    rskpActive = null;
}

function rskpLoadDetail(orderId) {
    // Reset state
    document.getElementById('rskp-title').textContent = '#' + orderId + ' – …';
    document.getElementById('rskp-risk-badge').textContent = '';
    document.getElementById('rskp-risk-badge').className = 'rskp-risk-badge';
    ['rskp-d-confirmed','rskp-d-payment','rskp-d-total','rskp-d-sender'].forEach(function(id){
        document.getElementById(id).textContent = '…';
    });
    document.getElementById('rskp-receipt-wrap').innerHTML = '<div class="rskp-receipt-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg></div>';
    document.getElementById('rskp-receipt-actions').style.display = 'none';
    document.getElementById('rskp-checklist-wrap').innerHTML = '<div style="color:#94a3b8;font-size:12px;text-align:center;padding:20px 0">' + whpI18n.verifying + '</div>';
    document.getElementById('rskp-verdict-section').style.display = 'none';
    document.getElementById('rskp-oi-rows').innerHTML = '';

    jQuery.post(ajaxurl, {action:'wpaap_aipay_get_order_detail', nonce:rskpNonce, order_id:orderId}, function(res) {
        if (!res || !res.success) {
            document.getElementById('rskp-checklist-wrap').innerHTML = '<div style="color:#dc2626;font-size:12px;text-align:center;padding:16px">' + whpI18n.loadDataFailed + '</div>';
            return;
        }
        var d = res.data;
        rskpPopulate(d);
    }).fail(function(){
        document.getElementById('rskp-checklist-wrap').innerHTML = '<div style="color:#dc2626;font-size:12px;text-align:center;padding:16px">' + whpI18n.connectionFailed + '</div>';
    });
}

function rskpPopulate(d) {
    // Title
    document.getElementById('rskp-title').textContent = '#' + d.order_id + ' – ' + d.name;

    // Risk badge
    var ai = d.ai_result, riskPct = 0, lvl = '';
    if (ai && ai.risk_score) {
        riskPct = Math.round(parseFloat(ai.risk_score) * 100);
        lvl = riskPct >= 65 ? 'high' : (riskPct >= 35 ? 'medium' : 'low');
        var lbl = {high: whpI18n.riskHigh, medium: whpI18n.riskMedium, low: whpI18n.riskLow};
        var badge = document.getElementById('rskp-risk-badge');
        badge.textContent = '● ' + riskPct + '% ' + lbl[lvl];
        badge.className = 'rskp-risk-badge ' + lvl;
    }

    // Meta
    document.getElementById('rskp-d-confirmed').textContent = d.confirmed;
    document.getElementById('rskp-d-payment').textContent   = d.payment;
    var totalEl = document.getElementById('rskp-d-total');
    totalEl.textContent = d.total;
    totalEl.className   = 'rskp-meta-val amount';
    document.getElementById('rskp-d-sender').textContent    = d.sender;

    // Receipt
    var receiptWrap = document.getElementById('rskp-receipt-wrap');
    if (d.receipt) {
        receiptWrap.innerHTML = '<img class="rskp-receipt-img" id="rskp-img" src="' + d.receipt + '" alt="Biên lai">';
        document.getElementById('rskp-receipt-actions').style.display = 'flex';
        document.getElementById('rskp-btn-view-orig').href     = d.receipt;
        document.getElementById('rskp-btn-download').href      = d.receipt;
        document.getElementById('rskp-btn-zoom').onclick       = function(){ rskpLightbox(d.receipt); };
        document.getElementById('rskp-img').onclick = function(){ rskpLightbox(d.receipt); };
    } else {
        receiptWrap.innerHTML = '<div class="rskp-receipt-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="13" y2="13"/></svg><span>' + whpI18n.noReceiptImage + '</span></div>';
    }

    // Verify btn
    var vBtn = document.getElementById('rskp-verify-btn');
    vBtn.dataset.oid = d.order_id;

    // AI checklist
    var cl = document.getElementById('rskp-checklist-wrap');
    if (ai && ai.checks && ai.checks.length) {
        var html = '<div class="rskp-checklist">';
        ai.checks.forEach(function(c) {
            var ok = c.passed ? 'pass' : 'fail';
            html += '<div class="rskp-check-item">'
                  + '<div class="rskp-check-icon ' + ok + '">' + (c.passed ? '✓' : '✗') + '</div>'
                  + '<span class="rskp-check-label">' + escH(c.label||c.check||'') + '</span>'
                  + '<span class="rskp-check-val ' + ok + '">' + escH(c.value||'') + '</span>'
                  + '</div>';
        });
        // Confidence row
        if (ai.ocr_confidence != null) {
            var conf = Math.round(parseFloat(ai.ocr_confidence)*100);
            html += '<div style="display:flex;align-items:center;gap:8px;padding-top:8px;margin-top:6px;border-top:1px solid #f1f5f9;">'
                  + '<span style="flex:1;font-size:12.5px;color:#374151;">' + whpI18n.ocrConfidence + '</span>'
                  + '<div style="width:50px;height:5px;background:#e2e8f0;border-radius:6px;overflow:hidden"><div style="height:100%;width:'+conf+'%;background:#10b981;border-radius:6px;"></div></div>'
                  + '<span style="font-size:12px;font-weight:700;color:#059669;min-width:28px;text-align:right">'+conf+'%</span>'
                  + '</div>';
        }
        html += '</div>';
        cl.innerHTML = html;

        // Verdict
        if (ai.verdict_reason || ai.verdict) {
            var vs = document.getElementById('rskp-verdict-section');
            vs.style.display = 'block';
            document.getElementById('rskp-verdict-reason').textContent = ai.verdict_reason || '';
            var vv = ai.verdict || '';
            var vmap = {valid: whpI18n.verdictValid, suspicious: whpI18n.verdictSuspicious, invalid: whpI18n.verdictInvalid};
            document.getElementById('rskp-verdict-badge').textContent = vmap[vv] || vv;
            document.getElementById('rskp-verdict-badge').className   = 'rskp-verdict-badge ' + vv;
        }
    } else {
        cl.innerHTML = '<div style="color:#94a3b8;font-size:12px;text-align:center;padding:20px 0">' + whpI18n.noAnalysisResult + '</div>';
    }

    // Order info
    var status = d.status || '';
    var oi = document.getElementById('rskp-oi-rows');
    var rows = [
        [whpI18n.orderId,    '#' + d.order_id, ''],
        [whpI18n.customer,   d.name,            ''],
        ['Email',            d.email,           ''],
        [whpI18n.phone,      d.phone || '—',    ''],
        [whpI18n.orderValue, d.total,           'amount'],
        [whpI18n.status,     d.status_lbl,      status],
    ];
    oi.innerHTML = rows.map(function(r){
        return '<div class="rskp-oi-row"><span class="rskp-oi-key">'+escH(r[0])+'</span>'
             + '<span class="rskp-oi-val '+escH(r[2])+'">'+escH(r[1])+'</span></div>';
    }).join('');

    // Action buttons context
    document.querySelectorAll('.rskp-act-btn[data-type]').forEach(function(b){ b.dataset.oid = d.order_id; });
    document.getElementById('rskp-oi-link').href = d.order_url;
}

function escH(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function rskpLightbox(src) {
    var lb = document.getElementById('rskp-lightbox');
    document.getElementById('rskp-lb-img').src = src;
    lb.classList.add('open');
}

// Bind eye icon + dropdown "Xem chi tiết"
document.querySelectorAll('.rsk3-btn-view').forEach(function(btn){
    btn.addEventListener('click', function(e){
        e.preventDefault();
        var row = btn.closest('.rsk3-row');
        if (row) rskpOpen(row.dataset.orderId);
    });
});
document.getElementById('rsk3-dd-view')?.addEventListener('click', function(){
    if (ddOid) { dd.style.display='none'; rskpOpen(ddOid); }
});

// Popup event delegation — popup HTML is rendered after this script block
document.addEventListener('click', function(e) {
    // Overlay backdrop
    if (e.target.id === 'rskp-overlay') { rskpClose(); return; }
    // Close button
    if (e.target.closest('#rskp-close-btn')) { rskpClose(); return; }
    // Lightbox close
    if (e.target.closest('#rskp-lb-close')) { document.getElementById('rskp-lightbox').classList.remove('open'); return; }
    // Verify button
    if (e.target.closest('#rskp-verify-btn')) {
        var vbtn = document.getElementById('rskp-verify-btn');
        var oid = vbtn && vbtn.dataset.oid;
        if (!oid) return;
        var origHtml = vbtn.innerHTML;
        vbtn.disabled = true;
        vbtn.innerHTML = '<svg class="rskp-spinner" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>' + whpI18n.verifyingAI;
        jQuery.post(ajaxurl, {action:'wpaap_aipay_verify', nonce:rskpNonce, order_id:oid}, function(res){
            vbtn.disabled = false;
            vbtn.innerHTML = origHtml;
            if (res && res.success) {
                rskpLoadDetail(oid);
            } else {
                wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.cannotVerifyNoReceipt, 'error');
            }
        }).fail(function(){
            vbtn.disabled = false;
            vbtn.innerHTML = origHtml;
            wpaapToast(whpI18n.connectionFailed, 'error');
        });
        return;
    }
    // Action buttons
    var actBtn = e.target.closest('.rskp-act-btn[data-type]');
    if (actBtn) {
        var type = actBtn.dataset.type;
        var oid  = actBtn.dataset.oid || rskpActive;
        if (!oid) return;
        if (type === 'note') {
            var wrap = document.getElementById('rskp-note-wrap');
            wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
            return;
        }
        var msgs = {confirm: whpI18n.confirmPayment, suspect: whpI18n.markSuspicious, reject: whpI18n.rejectPayment, request_receipt: whpI18n.requestReceipt};
        var _popBtn = actBtn;
        var _popOrigHtml = actBtn.innerHTML;
        var _popSuccessMsgs = {confirm: whpI18n.paymentConfirmed2, suspect: whpI18n.markedSuspicious, reject: whpI18n.paymentRejected, request_receipt: whpI18n.receiptRequestSent};
        wpaapConfirm(msgs[type] + ' đơn #' + oid + '?', function() {
            _popBtn.disabled = true;
            _popBtn.innerHTML = '<svg style="animation:rskp-spin .8s linear infinite" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
            jQuery.post(ajaxurl, {action:'wpaap_aipay_order_action', nonce:rskpNonce, order_id:oid, type:type}, function(res){
                _popBtn.disabled = false;
                _popBtn.innerHTML = _popOrigHtml;
                if (res && res.success) {
                    wpaapToast(_popSuccessMsgs[type] || whpI18n.actionSuccess, 'success');
                    rskpLoadDetail(oid);
                    setTimeout(function(){ location.reload(); }, 1500);
                } else {
                    wpaapToast((res.data && res.data.message) ? res.data.message : whpI18n.errorOccurred, 'error');
                }
            }).fail(function(){
                _popBtn.disabled = false;
                _popBtn.innerHTML = _popOrigHtml;
                wpaapToast(whpI18n.connectionFailed, 'error');
            });
        });
        return;
    }
    // Note submit
    if (e.target.closest('#rskp-note-submit')) {
        var oid  = rskpActive;
        var note = (document.getElementById('rskp-note-input').value || '').trim();
        if (!oid || !note) return;
        jQuery.post(ajaxurl, {action:'wpaap_aipay_order_action', nonce:rskpNonce, order_id:oid, type:'note', note:note}, function(res){
            if (res && res.success) {
                document.getElementById('rskp-note-input').value = '';
                document.getElementById('rskp-note-wrap').style.display = 'none';
                wpaapToast(whpI18n.noteSaved, 'success');
            }
        });
    }
});
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') {
        var lb = document.getElementById('rskp-lightbox');
        if (lb && lb.classList.contains('open')) { lb.classList.remove('open'); return; }
        rskpClose();
    }
});

})();
</script>

<!-- ═══ RISK DETAIL POPUP ════════════════════════════════════════════ -->
<div class="rskp-overlay" id="rskp-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Chi tiết giao dịch rủi ro', 'whp'); ?>">
  <div class="rskp-modal">

    <!-- Header -->
    <div class="rskp-head">
      <div class="rskp-head-left">
        <span class="rskp-title" id="rskp-title">#— – —</span>
        <span class="rskp-risk-badge" id="rskp-risk-badge"></span>
      </div>
      <button class="rskp-close" id="rskp-close-btn" aria-label="<?php esc_attr_e('Đóng', 'whp'); ?>">×</button>
    </div>

    <!-- Meta row -->
    <div class="rskp-meta">
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Thời gian', 'whp'); ?></div><div class="rskp-meta-val" id="rskp-d-confirmed">—</div></div>
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Phương thức', 'whp'); ?></div><div class="rskp-meta-val" id="rskp-d-payment">—</div></div>
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Số tiền', 'whp'); ?></div><div class="rskp-meta-val amount" id="rskp-d-total">—</div></div>
      <div class="rskp-meta-item"><div class="rskp-meta-lbl"><?php esc_html_e('Người gửi', 'whp'); ?></div><div class="rskp-meta-val" id="rskp-d-sender">—</div></div>
    </div>

    <!-- Body -->
    <div class="rskp-body">

      <!-- Main: receipt + AI -->
      <div class="rskp-main">
        <!-- Receipt -->
        <div class="rskp-receipt-panel">
          <div class="rskp-panel-hd"><?php esc_html_e('Ảnh biên lai (AI Vision)', 'whp'); ?></div>
          <div id="rskp-receipt-wrap">
            <div class="rskp-receipt-placeholder"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
          </div>
          <div class="rskp-receipt-actions" id="rskp-receipt-actions" style="display:none">
            <a class="rskp-receipt-btn" id="rskp-btn-view-orig" href="#" target="_blank">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg><?php esc_html_e('Xem ảnh gốc', 'whp'); ?>
            </a>
            <a class="rskp-receipt-btn" id="rskp-btn-download" href="#" download>
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg><?php esc_html_e('Tải ảnh', 'whp'); ?>
            </a>
            <button class="rskp-receipt-btn" id="rskp-btn-zoom">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg><?php esc_html_e('Phóng to', 'whp'); ?>
            </button>
          </div>
          <button class="rskp-btn-verify" id="rskp-verify-btn" data-oid="">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            <?php esc_html_e('Xác minh bằng AI', 'whp'); ?>
          </button>
        </div>

        <!-- AI Results -->
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

      <!-- Sidebar -->
      <div class="rskp-sidebar">

        <!-- Actions -->
        <div class="rskp-sc">
          <div class="rskp-sc-title"><?php esc_html_e('Hành động nhanh', 'whp'); ?></div>
          <div class="rskp-act-btns">
            <button class="rskp-act-btn confirm" data-type="confirm">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><?php esc_html_e('Xác nhận thanh toán', 'whp'); ?>
            </button>
            <button class="rskp-act-btn suspect" data-type="suspect">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/></svg><?php esc_html_e('Nghi ngờ / Kiểm tra thêm', 'whp'); ?>
            </button>
            <button class="rskp-act-btn reject" data-type="reject">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><?php esc_html_e('Từ chối thanh toán', 'whp'); ?>
            </button>
            <button class="rskp-act-btn outline" data-type="request_receipt">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.63"/></svg><?php esc_html_e('Yêu cầu gửi lại biên lai', 'whp'); ?>
            </button>
            <button class="rskp-act-btn outline" data-type="note">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg><?php esc_html_e('Ghi chú nội bộ', 'whp'); ?>
            </button>
          </div>
          <div class="rskp-note-wrap" id="rskp-note-wrap">
            <textarea class="rskp-note-input" id="rskp-note-input" placeholder="<?php esc_attr_e('Nhập ghi chú nội bộ…', 'whp'); ?>"></textarea>
            <button class="rskp-note-submit" id="rskp-note-submit"><?php esc_html_e('Lưu ghi chú', 'whp'); ?></button>
          </div>
        </div>

        <!-- Order info -->
        <div class="rskp-sc">
          <div class="rskp-sc-title"><?php esc_html_e('Thông tin đơn hàng', 'whp'); ?></div>
          <div class="rskp-oi-rows" id="rskp-oi-rows"></div>
          <a class="rskp-oi-link" id="rskp-oi-link" href="#" target="_blank"><?php esc_html_e('Xem chi tiết đơn hàng →', 'whp'); ?></a>
        </div>

      </div><!-- sidebar -->
    </div><!-- body -->
  </div><!-- modal -->
</div><!-- overlay -->

<!-- Lightbox -->
<div class="rskp-lightbox" id="rskp-lightbox">
  <button class="rskp-lightbox-close" id="rskp-lb-close">×</button>
  <img id="rskp-lb-img" src="" alt="<?php esc_attr_e('Biên lai phóng to', 'whp'); ?>">
</div>

<?php
}
