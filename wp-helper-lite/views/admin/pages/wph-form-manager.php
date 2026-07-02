<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function wph_form_manager_page_layout() {
    $nonce    = wp_create_nonce( 'wph_fm_nonce' );
    // settings page hidden — redirect to list
    $sub      = isset( $_GET['fmsub'] ) && $_GET['fmsub'] !== 'settings' ? sanitize_key( $_GET['fmsub'] ) : 'list';
    $base_url = admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=form-manager' );

    // Auto-mark read when opening detail
    if ( $sub === 'detail' && ! empty( $_GET['id'] ) ) {
        $detail_id = absint( $_GET['id'] );
        $row = wph_fm_get_submission( $detail_id );
        if ( $row && $row->status === 'new' ) {
            wph_fm_update_status( $detail_id, 'read' );
            $row->status = 'read';
        } elseif ( $row && $row->status === 'replied' ) {
            wph_fm_update_status( $detail_id, 'processing' );
            $row->status = 'processing';
        }
    }

    $stats       = wph_fm_get_stats();
    $forms_list  = wph_fm_get_forms_list();
    $settings    = get_option( 'wph_fm_settings', array() );
    $smtp_info   = function_exists( 'wph_el_detect_smtp' ) ? wph_el_detect_smtp() : array( 'active' => false );
    $smtp_active = ! empty( $smtp_info['active'] );
    $smtp_source = $smtp_info['source'] ?? '';

    // Extra stats for sidebar
    global $wpdb;
    $today_count   = (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}wph_form_submissions WHERE DATE(created_at)=%s",current_time('Y-m-d')));
    $week_count    = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wph_form_submissions WHERE created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY)");
    $month_count   = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wph_form_submissions WHERE created_at >= DATE_SUB(NOW(),INTERVAL 30 DAY)");
    $unread_count  = (int)$wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wph_form_submissions WHERE status IN('new','processing','replied')");
    $total_forms   = count($forms_list);
    $top_forms     = $wpdb->get_results("SELECT form_title,COUNT(*) as cnt FROM {$wpdb->prefix}wph_form_submissions GROUP BY form_id ORDER BY cnt DESC LIMIT 5");
    $status_counts = $wpdb->get_results("SELECT status,COUNT(*) as cnt FROM {$wpdb->prefix}wph_form_submissions GROUP BY status");
    $sc_map = array();
    foreach($status_counts as $sc) $sc_map[$sc->status] = $sc->cnt;

    // Detect active form plugins
    $plugin_map = array(
        'cf7'     => array( 'label' => 'Contact Form 7',   'detect' => class_exists( 'WPCF7' ) ),
        'wpforms' => array( 'label' => 'WPForms',          'detect' => function_exists( 'wpforms' ) ),
        'gf'      => array( 'label' => 'Gravity Forms',    'detect' => class_exists( 'GFForms' ) ),
        'nf'      => array( 'label' => 'Ninja Forms',      'detect' => class_exists( 'Ninja_Forms' ) ),
        'ff'      => array( 'label' => 'Fluent Forms',     'detect' => defined( 'FLUENTFORM' ) ),
        'frm'     => array( 'label' => 'Formidable Forms', 'detect' => class_exists( 'FrmHooksController' ) ),
        'wsf'     => array( 'label' => 'WS Form',          'detect' => class_exists( 'WS_Form' ) ),
    );
    ?>
    <style>
    .wph-fm { font-family:inherit; max-width:1200px; margin:0 auto; padding:0 0 40px; box-sizing:border-box; }

    /* ── Header ── */
    .wph-fm-header { position:relative; background:linear-gradient(100deg,#fff 0%,#f0f4ff 45%,#e8f0fd 100%); border-radius:20px; box-shadow:0 4px 24px rgba(56,88,233,.1),0 0 0 1px #e0e7ff; margin-bottom:20px; overflow:hidden; min-height:168px; display:flex; align-items:stretch; }
    .wph-fm-header-left { position:relative; z-index:2; padding:32px 36px; display:flex; flex-direction:column; justify-content:center; gap:14px; max-width:500px; flex-shrink:0; }
    .wph-fm-header-title-row { display:flex; align-items:center; gap:14px; }
    .wph-fm-header-icon { width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#7c3aed,#6d28d9);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(124,58,237,.3);flex-shrink:0; }
    .wph-fm-header-text h2 { font-size:20px;font-weight:700;color:#0f172a;margin:0 0 4px; }
    .wph-fm-header-text p  { font-size:13.5px;color:#475569;margin:0;line-height:1.5; }
    .wph-fm-header-right { position:absolute;inset:0 0 0 38%;pointer-events:none;overflow:hidden; }

    /* ── 5 stat cards ── */
    .wph-fm-stats { display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px; }
    @media(max-width:1100px){.wph-fm-stats{grid-template-columns:repeat(3,1fr);}}
    @media(max-width:700px){.wph-fm-stats{grid-template-columns:repeat(2,1fr);}}
    .wph-fm-stat { background:#fff;border-radius:14px;padding:16px 18px;box-shadow:0 1px 6px rgba(0,0,0,.06);border:1px solid #f1f5f9;display:flex;align-items:center;gap:13px; }
    .wph-fm-stat-icon { width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .wph-fm-stat-val { font-size:22px;font-weight:800;line-height:1;letter-spacing:-.5px; }
    .wph-fm-stat-lbl { font-size:11.5px;color:#64748b;margin-top:2px; }
    .wph-fm-stat-sub { font-size:11px;color:#94a3b8;margin-top:1px; }

    /* ── 2-col layout ── */
    .wph-fm-body { display:grid;grid-template-columns:1fr 270px;gap:16px;align-items:start; }
    @media(max-width:960px){.wph-fm-body{grid-template-columns:1fr;}}

    /* ── Filters ── */
    .wph-fm-filters { display:flex;flex-wrap:nowrap;gap:8px;align-items:center;padding:12px 14px;overflow-x:auto; }
    .wph-fm-filters input[type=text] { flex:1;min-width:180px;border:1px solid #e2e8f0;border-radius:8px;padding:0 12px;font-size:13px;height:36px;background:#fff;box-shadow:none;outline:none;color:#0f172a; }
    .wph-fm-filters input[type=text]:focus { border-color:#7c3aed;box-shadow:0 0 0 2px rgba(124,58,237,.12); }
    /* ── Custom dropdown (same pattern as contact.php) ── */
    .wph-fm-dd { position:relative;user-select:none;flex-shrink:0; }
    .wph-fm-dd-trigger { display:flex;align-items:center;gap:7px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:0 10px;height:36px;cursor:pointer;font-size:13px;color:#374151;transition:border-color .2s,box-shadow .2s;min-width:140px; }
    .wph-fm-dd-trigger:hover { border-color:#94a3b8; }
    .wph-fm-dd.open .wph-fm-dd-trigger { border-color:#7c3aed;box-shadow:0 0 0 2px rgba(124,58,237,.12); }
    .wph-fm-dd-trigger .dd-label { flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .wph-fm-dd-trigger .dd-chevron { color:#94a3b8;transition:transform .2s;flex-shrink:0; }
    .wph-fm-dd.open .dd-chevron { transform:rotate(180deg); }
    .wph-fm-dd-dot { width:8px;height:8px;border-radius:50%;flex-shrink:0;display:inline-block;transition:background .2s; }
    .wph-fm-dd-menu { display:none;position:fixed;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 4px 6px -1px rgba(0,0,0,.08),0 10px 24px rgba(0,0,0,.12);z-index:999999;min-width:160px;overflow:hidden; }
    .wph-fm-dd.open .wph-fm-dd-menu { display:block; }
    .wph-fm-dd-opt { display:flex;align-items:center;gap:8px;padding:8px 12px;cursor:pointer;font-size:13px;color:#374151;transition:background .12s; }
    .wph-fm-dd-opt:hover { background:#f8fafc; }
    .wph-fm-dd-opt.selected { background:#f5f3ff;color:#7c3aed;font-weight:600; }
    .wph-fm-dd-opt.selected .wph-fm-dd-dot { box-shadow:0 0 0 2px rgba(124,58,237,.2); }
    /* Date range popover */
    .wph-fm-daterange-wrap{position:relative;}
    .wph-fm-daterange{display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;font-size:12px;color:#374151;cursor:pointer;white-space:nowrap;transition:border-color .15s,color .15s;height:36px;box-sizing:border-box;}
    .wph-fm-daterange:hover,.wph-fm-daterange.open{border-color:#7c3aed;color:#7c3aed;}
    .wph-fm-daterange.is-active{border-color:#7c3aed;color:#7c3aed;}
    .wph-fm-date-popover{display:none;position:fixed;z-index:100000;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 8px 28px rgba(0,0,0,.13);padding:14px 16px;min-width:230px;}
    .wph-fm-date-popover.open{display:block;}
    .wph-fm-date-row{display:flex;flex-direction:column;gap:4px;margin-bottom:10px;}
    .wph-fm-date-row label{font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;}
    .wph-fm-date-row input[type="date"]{width:100%;height:32px;border:1px solid #e2e8f0!important;border-radius:7px!important;padding:0 8px!important;font-size:12px!important;color:#374151!important;background:#f8fafc!important;box-shadow:none!important;cursor:pointer;}
    .wph-fm-date-row input[type="date"]:focus{border-color:#7c3aed!important;outline:none!important;}
    .wph-fm-date-actions{display:flex;gap:7px;margin-top:6px;}
    .wph-fm-date-apply{flex:1;padding:7px 12px;border:none;border-radius:7px;background:#7c3aed;color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;}
    .wph-fm-date-apply:hover{background:#6d28d9;}
    .wph-fm-date-clear{padding:7px 10px;border:1px solid #e2e8f0;border-radius:7px;background:#fff;color:#64748b;font-size:12px;cursor:pointer;font-family:inherit;}
    .wph-fm-date-clear:hover{background:#f8fafc;}

    /* ── Buttons ── */
    .wph-fm-btn { padding:7px 14px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:600;height:36px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;white-space:nowrap; }
    .wph-fm-btn-primary  { background:#2563eb;color:#fff; }
    .wph-fm-btn-primary:hover  { background:#1d4ed8; }
    .wph-fm-btn-purple   { background:#7c3aed;color:#fff; }
    .wph-fm-btn-purple:hover   { background:#6d28d9; }
    .wph-fm-btn-outline  { background:#fff;color:#64748b;border:1px solid #e2e8f0; }
    .wph-fm-btn-outline:hover  { background:#f8fafc; }
    .wph-fm-btn-danger   { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }
    .wph-fm-btn-sm { padding:5px 9px;font-size:12px;height:28px;border-radius:6px; }
    .wph-fm-btn-icon { padding:5px 7px;border-radius:6px;border:1px solid #e2e8f0;background:#fff;cursor:pointer;display:inline-flex;align-items:center;color:#64748b;transition:all .15s; }
    .wph-fm-btn-icon:hover { background:#f8fafc; }
    .wph-fm-btn-icon.red:hover { background:#fef2f2;border-color:#fecaca;color:#dc2626; }

    /* ── Bulk bar ── */
    .wph-fm-bulk-bar { display:flex;align-items:center;gap:8px;padding:9px 14px;background:#f5f3ff;border-radius:10px;margin-bottom:10px;border:1px solid #ddd6fe; }
    .wph-fm-bulk-btn { padding:5px 16px;border-radius:7px;font-size:13px;font-weight:600;background:#7c3aed;color:#fff;border:none;cursor:pointer;height:32px; }
    /* Custom dropdown inside bulk bar — smaller height to fit */
    .wph-fm-bulk-bar .wph-fm-dd-trigger { height:32px;min-width:210px;border-color:#c4b5fd;background:#fff;font-size:13px; }
    .wph-fm-bulk-bar .wph-fm-dd.open .wph-fm-dd-trigger { border-color:#7c3aed;box-shadow:0 0 0 2px rgba(124,58,237,.12); }
    .wph-fm-bulk-bar .wph-fm-dd-opt:last-child { border-top:1px solid #fee2e2; }

    /* ── Table ── */
    .wph-fm-card { background:#fff;border-radius:14px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #f1f5f9;overflow:hidden; }
    .wph-fm-table-wrap { overflow-x:auto; }
    .wph-fm-table { width:100%;border-collapse:collapse;font-size:13px; }
    .wph-fm-table th { background:#f8fafc;padding:10px 12px;text-align:left;font-weight:700;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;border-bottom:1px solid #e2e8f0;white-space:nowrap; }
    .wph-fm-table td { padding:11px 12px;border-bottom:1px solid #f1f5f9;vertical-align:middle; }
    .wph-fm-table tr:last-child td { border-bottom:none; }
    .wph-fm-table tr:hover td { background:#fafafa; }
    .wph-fm-table tr.is-new td { background:#fffbeb; }
    .wph-fm-table tr.is-replied td { background:#fdf4ff; }
    .wph-fm-table tr.is-replied:hover td { background:#f5e9ff; }
    .wph-fm-col-check { width:36px; }
    .wph-fm-col-id { width:56px;color:#94a3b8;font-size:12px; }

    /* ── Pagination ── */
    .wph-fm-pagination { display:flex;align-items:center;justify-content:space-between;padding:11px 14px;border-top:1px solid #f1f5f9;font-size:13px;color:#64748b; }
    .wph-fm-page-links { display:flex;gap:3px;flex-wrap:wrap; }
    .wph-fm-page-links a,.wph-fm-page-links span,.wph-fm-page-links button { padding:4px 9px;border-radius:6px;border:1px solid #e2e8f0;text-decoration:none;color:#475569;font-size:12px;background:#fff;cursor:pointer; }
    .wph-fm-page-links span.current { background:#7c3aed;color:#fff;border-color:#7c3aed;font-weight:700; }
    .wph-fm-page-links a:hover { background:#f5f3ff; }
    .wph-fm-page-ellipsis { padding:4px 6px;color:#94a3b8;font-size:12px; }

    /* ── Sidebar ── */
    .wph-fm-sidebar { display:flex;flex-direction:column;gap:12px; }
    .wph-fm-sb-card { background:#fff;border-radius:14px;padding:16px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #f1f5f9; }
    .wph-fm-sb-card h4 { font-size:13px;font-weight:700;color:#0f172a;margin:0 0 12px;display:flex;align-items:center;gap:7px; }
    .wph-fm-sb-row { display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f8fafc;font-size:13px; }
    .wph-fm-sb-row:last-child { border-bottom:none; }
    .wph-fm-sb-row-lbl { color:#64748b;font-size:12px; }
    .wph-fm-sb-row-val { font-weight:700;color:#0f172a; }
    .wph-fm-bar-row { display:flex;align-items:center;gap:8px;padding:5px 0;font-size:12px; }
    .wph-fm-bar-track { flex:1;background:#f1f5f9;border-radius:6px;height:7px;overflow:hidden; }
    .wph-fm-bar-fill { height:100%;border-radius:6px;background:linear-gradient(90deg,#7c3aed,#a78bfa); }
    .wph-fm-bar-name { min-width:70px;color:#374151;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
    .wph-fm-bar-count { min-width:30px;text-align:right;color:#64748b; }
    .wph-fm-status-dot { display:inline-block;width:8px;height:8px;border-radius:50%;margin-right:5px; }

    /* ── Detail ── */
    .wph-fm-detail-grid { display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start; }
    @media(max-width:900px){.wph-fm-detail-grid{grid-template-columns:1fr;}}
    .wph-fm-detail-card { background:#fff;border-radius:14px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.06);border:1px solid #f1f5f9;margin-bottom:14px; }
    .wph-fm-detail-card h3 { font-size:14px;font-weight:700;color:#0f172a;margin:0 0 14px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:8px; }
    .wph-fm-detail-row { display:flex;gap:10px;padding:7px 0;border-bottom:1px solid #f8fafc;font-size:13px; }
    .wph-fm-detail-row:last-child { border-bottom:none; }
    .wph-fm-detail-row dt { color:#64748b;min-width:120px;flex-shrink:0;font-weight:500; }
    .wph-fm-detail-row dd { color:#0f172a;margin:0;word-break:break-all; }

    /* ── Settings ── */
    .wph-fm-settings-grid { display:grid;grid-template-columns:1fr 1fr;gap:16px; }
    @media(max-width:800px){.wph-fm-settings-grid{grid-template-columns:1fr;}}
    .wph-fm-plugin-row { display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f1f5f9; }
    .wph-fm-plugin-row:last-child { border-bottom:none; }
    .wph-fm-plugin-name { font-size:13px;font-weight:600;color:#0f172a; }
    .wph-fm-plugin-status { font-size:11px; }
    .wph-fm-plugin-status.installed { color:#16a34a; }
    .wph-fm-plugin-status.missing   { color:#94a3b8; }
    .wph-fm-toggle { position:relative;display:inline-block;width:40px;height:22px; }
    .wph-fm-toggle input { opacity:0;width:0;height:0; }
    .wph-fm-toggle-slider { position:absolute;cursor:pointer;inset:0;background:#e2e8f0;border-radius:22px;transition:.2s; }
    .wph-fm-toggle-slider:before { position:absolute;content:"";height:16px;width:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.2s; }
    .wph-fm-toggle input:checked+.wph-fm-toggle-slider { background:#7c3aed; }
    .wph-fm-toggle input:checked+.wph-fm-toggle-slider:before { transform:translateX(18px); }

    /* ── Misc ── */
    .wph-fm-empty { text-align:center;padding:56px 20px;color:#94a3b8; }
    .wph-fm-notice { padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:12px;display:none; }
    .wph-fm-notice.success { background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0; }
    .wph-fm-notice.error   { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }
    @keyframes wph-spin { to { transform: rotate(360deg); } }
    /* Toast gửi thành công */
    #wph-fm-toast{position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(20px);background:#1e293b;color:#fff;font-size:13px;font-weight:600;padding:10px 22px;border-radius:24px;box-shadow:0 4px 16px rgba(0,0,0,.22);z-index:99999;opacity:0;transition:opacity .25s,transform .25s;pointer-events:none;white-space:nowrap;}
    #wph-fm-toast.show{opacity:1;transform:translateX(-50%) translateY(0);}
    /* ── Rich text editor ── */
    #fm-editor-toolbar{display:flex;align-items:center;gap:2px;padding:6px 8px;background:#f8fafc;border:1.5px solid #e2e8f0;border-bottom:none;border-radius:8px 8px 0 0;}
    #fm-editor-toolbar button{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border:none;border-radius:6px;background:transparent;cursor:pointer;color:#475569;transition:background .12s,color .12s;padding:0;}
    #fm-editor-toolbar button:hover{background:#e2e8f0;color:#1e293b;}
    #fm-editor-toolbar button.active{background:#ede9fe;color:#7c3aed;}
    .fm-tb-sep{width:1px;height:18px;background:#e2e8f0;margin:0 4px;flex-shrink:0;}
    #fm-conv-editor{min-height:120px;max-height:400px;overflow-y:auto;border:1.5px solid #e2e8f0;border-radius:0 0 8px 8px;padding:10px 14px;font-size:13px;font-family:inherit;line-height:1.7;color:#374151;outline:none;transition:border-color .15s;background:#fff;}
    #fm-conv-editor:focus{border-color:#7c3aed;}
    #fm-conv-editor:empty:before{content:attr(data-placeholder);color:#94a3b8;pointer-events:none;}
    #fm-conv-editor ul{margin:4px 0 4px 20px;padding:0;}
    #fm-conv-editor ol{margin:4px 0 4px 20px;padding:0;}
    #fm-conv-editor li{margin:2px 0;}
    #fm-conv-editor a{color:#7c3aed;}
    /* ── Confirm modal ── */
    .wph-fm-confirm-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:99999;display:flex;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(2px);}
    .wph-fm-confirm-modal{background:#fff;border-radius:16px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.2);}
    .wph-fm-confirm-head{padding:18px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
    .wph-fm-confirm-head h2{margin:0;font-size:16px;font-weight:700;color:#1e293b;}
    .wph-fm-confirm-foot{padding:14px 24px;border-top:1px solid #f1f5f9;display:flex;justify-content:flex-end;gap:10px;}
    .wph-fm-confirm-close{background:none;border:none;cursor:pointer;padding:4px;color:#64748b;line-height:1;display:flex;align-items:center;border-radius:6px;}
    .wph-fm-confirm-close:hover{background:#f1f5f9;}

    /* ── Conversation thread ── */
    .fm-conv-msg{margin-bottom:12px;}
    .fm-conv-msg:last-child{margin-bottom:0;}
    .fm-conv-meta{display:flex;align-items:center;gap:6px;font-size:11.5px;color:#94a3b8;margin-bottom:4px;}
    .fm-conv-meta strong{color:#475569;font-weight:600;}
    .fm-conv-body{font-size:13px;color:#374151;line-height:1.7;padding:10px 14px;border-radius:8px;}
    .fm-conv-outbound .fm-conv-meta{color:#7c3aed;}
    .fm-conv-outbound .fm-conv-meta strong{color:#6d28d9;}
    .fm-conv-outbound .fm-conv-body{background:#faf5ff;border-left:3px solid #7c3aed;}
    .fm-conv-inbound .fm-conv-meta{color:#2563eb;}
    .fm-conv-inbound .fm-conv-meta strong{color:#1d4ed8;}
    .fm-conv-inbound .fm-conv-body{background:#eff6ff;border-left:3px solid #2563eb;}
    </style>

    <div id="wph-fm-toast"></div>
    <div class="wph-fm">
    <div id="wph-fm-notice" class="wph-fm-notice"></div>

    <?php /* ─── DETAIL PAGE ─── */ ?>
    <?php if ( $sub === 'detail' ): ?>
    <?php
    if ( empty( $_GET['id'] ) || ! ( $row = wph_fm_get_submission( absint( $_GET['id'] ) ) ) ):
    ?>
        <div class="wph-fm-empty"><p><?php esc_html_e('Không tìm thấy liên hệ.', 'whp'); ?></p>
        <a href="<?php echo esc_url($base_url.'&fmsub=list'); ?>" class="wph-fm-btn wph-fm-btn-purple" style="margin-top:10px;display:inline-flex;"><?php esc_html_e('← Quay lại', 'whp'); ?></a></div>
    <?php else:
        $fields = json_decode( $row->submission_data, true ) ?: array();
        $plugin_labels = array( 'cf7'=>'Contact Form 7','wpforms'=>'WPForms','gf'=>'Gravity Forms','nf'=>'Ninja Forms','ff'=>'Fluent Forms','frm'=>'Formidable','wsf'=>'WS Form' );
    ?>
    <div style="margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <a href="<?php echo esc_url($base_url.'&fmsub=list'); ?>" class="wph-fm-btn wph-fm-btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            <?php esc_html_e('Danh sách', 'whp'); ?>
        </a>
        <h2 style="margin:0;font-size:16px;font-weight:700;color:#0f172a;"><?php echo esc_html(sprintf(__('Liên hệ #%d', 'whp'), $row->id)); ?></h2>
        <?php echo wph_fm_status_badge($row->status); ?>
    </div>
    <?php
    $conv_total     = wph_fm_count_conversations( $row->id );
    $conversations  = wph_fm_get_conversations_paged( $row->id, 6, 0 );
    $customer_email = $row->customer_email;
    $customer_name  = $row->customer_name ?: $row->customer_email;
    ?>
    <div class="wph-fm-detail-grid">
        <div>
            <div class="wph-fm-detail-card">
                <h3>
                    <span style="width:26px;height:26px;border-radius:7px;background:#ede9fe;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </span>
                    <?php esc_html_e('Dữ liệu form', 'whp'); ?>
                </h3>
                <?php
                $skip_patterns = array('g-recaptcha-response', '_wpcf7_recaptcha_response');
                foreach($fields as $k=>$v):
                    if(in_array($k, $skip_patterns, true)) continue;
                    if(strpos($k, '_wpcf7') === 0) continue;
                    if(stripos($k, 'recaptcha') !== false) continue;
                    if(stripos($k, 'captcha') !== false) continue;
                    $vv=is_array($v)?implode(', ',$v):$v;
                    if(!trim($vv))continue;
                ?>
                <div class="wph-fm-detail-row"><dt><?php echo esc_html(str_replace(array('-','_'),' ',$k)); ?></dt><dd><?php echo nl2br(esc_html($vv)); ?></dd></div>
                <?php endforeach; ?>
                <?php if(empty($fields)): ?><p style="color:#94a3b8;font-size:13px;"><?php esc_html_e('Không có dữ liệu.', 'whp'); ?></p><?php endif; ?>
            </div>

            <!-- Hội thoại phản hồi (left column) -->
            <div class="wph-fm-detail-card" id="fm-conv-card">
                <h3>
                    <span style="width:26px;height:26px;border-radius:7px;background:#eff6ff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    </span>
                    <?php esc_html_e('Hội thoại phản hồi', 'whp'); ?>
                    <?php if($customer_email): ?><span style="font-size:12px;font-weight:400;color:#64748b;margin-left:6px;">→ <?php echo esc_html($customer_email); ?></span><?php endif; ?>
                </h3>
                <?php if($conv_total > 6): ?>
                <div id="fm-conv-load-more-wrap" style="text-align:center;margin-bottom:12px;">
                    <button type="button" id="fm-conv-load-more"
                        data-submission-id="<?php echo intval($row->id); ?>"
                        data-offset="6"
                        onclick="wphFmLoadMoreConv(this)"
                        style="background:#f1f5f9;border:1.5px solid #e2e8f0;color:#475569;font-size:12.5px;font-weight:600;padding:6px 16px;border-radius:20px;cursor:pointer;transition:background .15s;">
                        <?php echo esc_html(sprintf(__('Xem thêm (%d tin nhắn trước)', 'whp'), intval($conv_total - 6))); ?>
                    </button>
                </div>
                <?php endif; ?>
                <div id="fm-conv-thread" style="margin-bottom:16px;">
                <?php if(empty($conversations)): ?>
                    <p style="color:#94a3b8;font-size:13px;text-align:center;padding:20px 0;"><?php esc_html_e('Chưa có phản hồi nào.', 'whp'); ?></p>
                <?php else: ?>
                    <?php foreach($conversations as $c): ?>
                    <div class="fm-conv-msg fm-conv-<?php echo esc_attr($c->direction); ?>" data-id="<?php echo $c->id; ?>">
                        <div class="fm-conv-meta">
                            <?php if($c->direction==='outbound'): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>
                            <?php else: ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9,17 4,12 9,7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>
                            <?php endif; ?>
                            <strong><?php echo esc_html($c->author_label); ?></strong>
                            <span><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($c->created_at))); ?></span>
                        </div>
                        <div class="fm-conv-body"><?php echo wp_kses_post($c->content); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>
                <?php if($customer_email): ?>
                <div id="fm-conv-compose">
                    <label style="font-size:11.5px;font-weight:700;color:#64748b;display:block;margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px;"><?php esc_html_e('Soạn phản hồi', 'whp'); ?></label>
                    <!-- Rich text toolbar -->
                    <div id="fm-editor-toolbar">
                        <button type="button" data-cmd="bold" title="Bold"><svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg></button>
                        <button type="button" data-cmd="italic" title="Italic"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg></button>
                        <button type="button" data-cmd="underline" title="Underline"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"/><line x1="4" y1="21" x2="20" y2="21"/></svg></button>
                        <span class="fm-tb-sep"></span>
                        <button type="button" data-cmd="insertUnorderedList" title="<?php esc_attr_e('Danh sách chấm', 'whp'); ?>"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/></svg></button>
                        <button type="button" data-cmd="insertOrderedList" title="<?php esc_attr_e('Danh sách số', 'whp'); ?>"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><text x="1" y="8" font-size="7" fill="currentColor" stroke="none">1</text><text x="1" y="14" font-size="7" fill="currentColor" stroke="none">2</text><text x="1" y="20" font-size="7" fill="currentColor" stroke="none">3</text></svg></button>
                        <span class="fm-tb-sep"></span>
                        <button type="button" data-cmd="createLink" title="<?php esc_attr_e('Chèn liên kết', 'whp'); ?>"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg></button>
                        <button type="button" data-cmd="removeFormat" title="<?php esc_attr_e('Xóa định dạng', 'whp'); ?>"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
                    </div>
                    <div id="fm-conv-editor" contenteditable="true" data-placeholder="<?php echo esc_attr(sprintf(__('Nhập nội dung phản hồi gửi tới %s…', 'whp'), $customer_name)); ?>"></div>
                    <div style="display:flex;justify-content:flex-end;margin-top:10px;">
                        <button class="wph-fm-btn wph-fm-btn-purple" onclick="wphFmSendReply(<?php echo $row->id; ?>)" id="fm-conv-send-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>
                            <?php esc_html_e('Gửi phản hồi', 'whp'); ?>
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <p style="color:#94a3b8;font-size:12.5px;background:#fefce8;border:1px solid #fde047;border-radius:8px;padding:10px 14px;"><?php esc_html_e('Không có email khách hàng — không thể gửi phản hồi.', 'whp'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <div class="wph-fm-detail-card">
                <h3>
                    <span style="width:26px;height:26px;border-radius:7px;background:#eff6ff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </span>
                    <?php esc_html_e('Thông tin', 'whp'); ?>
                </h3>
                <?php foreach(array(__('Form','whp')=>$row->form_title?:$row->form_id,__('Ngày gửi','whp')=>date_i18n('d/m/Y H:i',strtotime($row->created_at)),__('Họ tên','whp')=>$row->customer_name,__('Email','whp')=>$row->customer_email,__('SĐT','whp')=>$row->customer_phone) as $k=>$v): if(!trim($v??''))continue; ?>
                <div class="wph-fm-detail-row"><dt><?php echo esc_html($k);?></dt><dd><?php echo esc_html($v);?></dd></div>
                <?php endforeach; ?>
            </div>
            <div class="wph-fm-detail-card">
                <h3>
                    <span style="width:26px;height:26px;border-radius:7px;background:#faf5ff;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                    <?php esc_html_e('Trạng thái', 'whp'); ?>
                </h3>
                <?php
                $detail_statuses = array(
                    'new'        => array('dot'=>'#3b82f6','bg'=>'#eff6ff','label'=>__('Mới','whp'),            'desc'=>__('Chưa xử lý','whp')),
                    'read'       => array('dot'=>'#94a3b8','bg'=>'#f8fafc','label'=>__('Đã đọc','whp'),         'desc'=>__('Đã xem qua','whp')),
                    'processing' => array('dot'=>'#f59e0b','bg'=>'#fffbeb','label'=>__('Đang xử lý','whp'),     'desc'=>__('Đang tiến hành','whp')),
                    'replied'    => array('dot'=>'#9333ea','bg'=>'#fdf4ff','label'=>__('Khách phản hồi','whp'), 'desc'=>__('Khách đã trả lời','whp')),
                    'done'       => array('dot'=>'#22c55e','bg'=>'#f0fdf4','label'=>__('Hoàn thành','whp'),     'desc'=>__('Đã xử lý xong','whp')),
                    'spam'       => array('dot'=>'#ef4444','bg'=>'#fef2f2','label'=>__('Spam','whp'),           'desc'=>__('Đánh dấu rác','whp')),
                );
                ?>
                <input type="hidden" id="fm-detail-status" value="<?php echo esc_attr($row->status); ?>">
                <div id="fm-detail-status-picker" style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
                    <?php foreach($detail_statuses as $v=>$s):
                        $active = ($row->status === $v);
                    ?>
                    <div class="fm-status-opt <?php echo $active?'active':''; ?>"
                         data-value="<?php echo esc_attr($v); ?>"
                         data-dot="<?php echo esc_attr($s['dot']); ?>"
                         data-bg="<?php echo esc_attr($s['bg']); ?>"
                         onclick="wphFmPickStatus(this)"
                         style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:9px;cursor:pointer;border:1.5px solid <?php echo $active ? $s['dot'] : '#e2e8f0'; ?>;background:<?php echo $active ? $s['bg'] : '#fff'; ?>;transition:all .15s;">
                        <span style="width:10px;height:10px;border-radius:50%;background:<?php echo esc_attr($s['dot']); ?>;flex-shrink:0;box-shadow:0 0 0 3px <?php echo $active ? $s['bg'] : '#fff'; ?>,0 0 0 4px <?php echo esc_attr($s['dot']); ?>33;transition:box-shadow .15s;"></span>
                        <span style="flex:1;min-width:0;">
                            <span style="display:block;font-size:13px;font-weight:<?php echo $active?'700':'500'; ?>;color:<?php echo $active ? $s['dot'] : '#374151'; ?>;line-height:1.3;"><?php echo esc_html($s['label']); ?></span>
                            <span style="display:block;font-size:11px;color:#94a3b8;line-height:1.3;"><?php echo esc_html($s['desc']); ?></span>
                        </span>
                        <?php if($active): ?>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($s['dot']); ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="wph-fm-btn wph-fm-btn-purple" style="width:100%;margin-bottom:8px;justify-content:center;" onclick="wphFmSaveStatus(<?php echo $row->id;?>)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    <?php esc_html_e('Lưu trạng thái', 'whp'); ?>
                </button>
                <button class="wph-fm-btn wph-fm-btn-danger" style="width:100%;justify-content:center;" onclick="wphFmDeleteOne(<?php echo $row->id;?>,'<?php echo esc_js($base_url.'&fmsub=list');?>')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    <?php esc_html_e('Xóa liên hệ', 'whp'); ?>
                </button>
            </div>

            <!-- Kỹ thuật (sidebar) -->
            <div class="wph-fm-detail-card">
                <h3>
                    <span style="width:26px;height:26px;border-radius:7px;background:#f1f5f9;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                    </span>
                    <?php esc_html_e('Kỹ thuật', 'whp'); ?>
                </h3>
                <?php foreach(array('IP'=>$row->ip_address,'Plugin'=>$plugin_labels[$row->form_plugin]??$row->form_plugin,'URL'=>$row->submission_url,'Referrer'=>$row->referrer) as $k=>$v): if(!trim($v??''))continue; ?>
                <div class="wph-fm-detail-row"><dt><?php echo esc_html($k);?></dt><dd><?php echo esc_html($v);?></dd></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php endif; ?>

    <?php /* ─── SETTINGS PAGE ─── */ ?>
    <?php elseif ( $sub === 'settings' ):
        $s_plugins   = $settings['plugins'] ?? array();
        $s_retention = $settings['retention'] ?? 0;
        $s_max_logs  = $settings['max_logs'] ?? 0;
        $s_active    = $settings['active'] ?? '1';
    ?>
    <div style="margin-bottom:14px;">
        <a href="<?php echo esc_url($base_url); ?>" class="wph-fm-btn wph-fm-btn-outline"><?php esc_html_e('← Quay lại', 'whp'); ?></a>
    </div>
    <div class="wph-fm-settings-grid">
        <div class="wph-fm-detail-card">
            <h3>🔌 <?php esc_html_e('Tích hợp Form Plugins', 'whp'); ?></h3>
            <?php foreach($plugin_map as $key=>$info): ?>
            <div class="wph-fm-plugin-row">
                <div><div class="wph-fm-plugin-name"><?php echo esc_html($info['label']);?></div>
                <div class="wph-fm-plugin-status <?php echo $info['detect']?'installed':'missing';?>"><?php echo $info['detect'] ? '✓ ' . esc_html__('Đang hoạt động','whp') : '— ' . esc_html__('Chưa cài','whp');?></div></div>
                <label class="wph-fm-toggle"><input type="checkbox" class="fm-plugin-toggle" data-plugin="<?php echo esc_attr($key);?>" <?php checked(($s_plugins[$key]??'1')!=='0');?> <?php disabled(!$info['detect']);?>>
                <span class="wph-fm-toggle-slider"></span></label>
            </div>
            <?php endforeach; ?>
        </div>
        <div>
            <div class="wph-fm-detail-card">
                <h3>⚙️ <?php esc_html_e('Cài đặt chung', 'whp'); ?></h3>
                <div class="wph-fm-plugin-row">
                    <div><div class="wph-fm-plugin-name"><?php esc_html_e('Kích hoạt lưu dữ liệu', 'whp'); ?></div><div class="wph-fm-plugin-status installed"><?php esc_html_e('Bật để lưu form submissions', 'whp'); ?></div></div>
                    <label class="wph-fm-toggle"><input type="checkbox" id="fm-setting-active" <?php checked($s_active!=='0');?>><span class="wph-fm-toggle-slider"></span></label>
                </div>
                <div style="margin-top:14px;">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;"><?php esc_html_e('Thời gian lưu trữ', 'whp'); ?></label>
                    <select id="fm-setting-retention" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;font-size:13px;">
                        <?php foreach(array(0=>__('Không giới hạn','whp'),30=>__('30 ngày','whp'),90=>__('90 ngày','whp'),180=>__('180 ngày','whp')) as $v=>$l): ?>
                        <option value="<?php echo $v;?>" <?php selected($s_retention,$v);?>><?php echo $l;?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="margin-top:12px;">
                    <label style="font-size:13px;font-weight:600;display:block;margin-bottom:6px;"><?php esc_html_e('Giới hạn tối đa', 'whp'); ?></label>
                    <select id="fm-setting-maxlogs" style="width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;font-size:13px;">
                        <?php foreach(array(10000=>__('10.000 bản ghi','whp'),25000=>__('25.000 bản ghi','whp'),50000=>__('50.000 bản ghi','whp'),100000=>__('100.000 bản ghi','whp'),0=>__('Không giới hạn','whp')) as $v=>$l): ?>
                        <option value="<?php echo $v;?>" <?php selected($s_max_logs,$v);?>><?php echo $l;?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="wph-fm-btn wph-fm-btn-purple" style="width:100%;margin-top:14px;" onclick="wphFmSaveSettings()">💾 <?php esc_html_e('Lưu cài đặt', 'whp'); ?></button>
            </div>
        </div>
    </div>

    <?php /* ─── LIST PAGE (default) ─── */ ?>
    <?php else: ?>

    <?php /* Header */ ?>
    <div class="wph-fm-header">
        <div class="wph-fm-header-left">
            <div class="wph-fm-header-title-row">
                <div class="wph-fm-header-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                </div>
                <div class="wph-fm-header-text">
                    <h2><?php esc_html_e('Quản lý Form', 'whp'); ?></h2>
                </div>
            </div>
            <p style="font-size:13.5px;color:#475569;margin:0;line-height:1.5;margin-left:58px;"><?php esc_html_e('Theo dõi và quản lý dữ liệu khách hàng gửi từ', 'whp'); ?> <?php
                $active_plugins = array_filter($plugin_map, fn($p)=>$p['detect']);
                echo count($active_plugins) ? implode(', ', array_column($active_plugins,'label')) : 'form plugin';
            ?>.</p>
            <div style="display:flex;align-items:center;gap:10px;margin-left:58px;margin-top:4px;flex-wrap:wrap;">
            <?php if ( $smtp_active ) : ?>
                <span style="display:inline-flex;align-items:center;gap:5px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                    <span style="width:7px;height:7px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
                    SMTP: <?php echo esc_html( $smtp_source ); ?>
                </span>
            <?php else : ?>
                <span style="display:inline-flex;align-items:center;gap:5px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;">
                    <span style="width:7px;height:7px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
                    <?php esc_html_e('Chưa phát hiện SMTP', 'whp'); ?>
                </span>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=mb-wphelper-smtp&subtab=smtp' ) ); ?>" style="font-size:13px;color:#16a34a;font-weight:600;"><?php esc_html_e('Cấu hình ngay →', 'whp'); ?></a>
            <?php endif; ?>
            </div>
        </div>
        <div class="wph-fm-header-right">
            <svg viewBox="0 0 520 130" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs><linearGradient id="fmhbg" x1="0" y1="0" x2="1" y2="0"><stop offset="0%" stop-color="#f0f4ff" stop-opacity="0"/><stop offset="30%" stop-color="#ede9fe" stop-opacity=".6"/><stop offset="100%" stop-color="#ddd6fe" stop-opacity="1"/></linearGradient></defs>
                <rect width="520" height="130" fill="url(#fmhbg)"/>
                <!-- Person icon box -->
                <rect x="80" y="20" width="80" height="90" rx="10" fill="#fff" fill-opacity=".85"/>
                <circle cx="120" cy="52" r="18" fill="#e0e7ff"/>
                <path d="M100 90c0-11 9-20 20-20s20 9 20 20" stroke="#7c3aed" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <circle cx="120" cy="52" r="10" fill="#7c3aed" fill-opacity=".7"/>
                <!-- Mail icon box -->
                <rect x="190" y="30" width="80" height="60" rx="10" fill="#fff" fill-opacity=".85"/>
                <rect x="203" y="43" width="54" height="34" rx="4" fill="#e0e7ff"/>
                <path d="M203 47l27 18 27-18" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" fill="none"/>
                <!-- Pie chart box -->
                <rect x="300" y="18" width="90" height="92" rx="10" fill="#fff" fill-opacity=".85"/>
                <circle cx="345" cy="64" r="28" fill="#e0e7ff"/>
                <path d="M345 64 L345 36 A28 28 0 0 1 373 64 Z" fill="#7c3aed"/>
                <path d="M345 64 L373 64 A28 28 0 0 1 320 80 Z" fill="#a78bfa"/>
                <!-- Dots -->
                <circle cx="420" cy="35" r="5" fill="#7c3aed" fill-opacity=".25"/>
                <circle cx="445" cy="80" r="4" fill="#a78bfa" fill-opacity=".3"/>
                <circle cx="60"  cy="60" r="4" fill="#7c3aed" fill-opacity=".2"/>
            </svg>
        </div>
    </div>

    <?php /* 5 Stat cards */ ?>
    <div class="wph-fm-stats">
        <?php
        $sc = array(
            array('val'=>$total_forms,         'lbl'=>__('Tổng Form','whp'),    'sub'=>$total_forms.' '.esc_html__('loại form','whp'),'bg'=>'#f5f3ff','ic'=>'#7c3aed','svg'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>'),
            array('val'=>number_format($stats['total']),   'lbl'=>__('Tổng liên hệ','whp'), 'sub'=>'+'.number_format($week_count).' '.esc_html__('tuần này','whp'),'bg'=>'#eff6ff','ic'=>'#2563eb','svg'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'),
            array('val'=>$stats['new_count'],  'lbl'=>__('Liên hệ mới','whp'),  'sub'=>__('Hôm nay','whp'),'bg'=>'#f0fdf4','ic'=>'#16a34a','svg'=>'<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'),
            array('val'=>$stats['processing'], 'lbl'=>__('Đang xử lý','whp'),   'sub'=>__('Chờ phản hồi','whp'),'bg'=>'#fffbeb','ic'=>'#d97706','svg'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'),
            array('val'=>number_format($stats['done']),    'lbl'=>__('Hoàn thành','whp'),   'sub'=>'+'.number_format($month_count).' '.esc_html__('tháng này','whp'),'bg'=>'#f0fdf4','ic'=>'#16a34a','svg'=>'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>'),
        );
        foreach($sc as $c): ?>
        <div class="wph-fm-stat">
            <div class="wph-fm-stat-icon" style="background:<?php echo esc_attr($c['bg']);?>;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($c['ic']);?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $c['svg'];?></svg>
            </div>
            <div>
                <div class="wph-fm-stat-val" style="color:<?php echo esc_attr($c['ic']);?>;"><?php echo $c['val'];?></div>
                <div class="wph-fm-stat-lbl"><?php echo esc_html($c['lbl']);?></div>
                <div class="wph-fm-stat-sub"><?php echo esc_html($c['sub']);?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php
    $filter_status     = isset($_GET['fm_status'])     ? sanitize_key($_GET['fm_status'])            : '';
    $filter_form_id    = isset($_GET['fm_form_id'])    ? sanitize_text_field($_GET['fm_form_id'])    : '';
    $filter_date_from  = isset($_GET['fm_date_from'])  ? sanitize_text_field($_GET['fm_date_from'])  : '';
    $filter_date_to    = isset($_GET['fm_date_to'])    ? sanitize_text_field($_GET['fm_date_to'])    : '';
    $filter_search     = isset($_GET['fm_search'])     ? sanitize_text_field($_GET['fm_search'])     : '';
    $current_page      = max(1, intval($_GET['fm_page'] ?? 1));
    $per_page          = 20;

    $result = wph_fm_get_submissions(array(
        'status'    => $filter_status,
        'form_id'   => $filter_form_id,
        'date_from' => $filter_date_from,
        'date_to'   => $filter_date_to,
        'search'    => $filter_search,
        'per_page'  => $per_page,
        'page'      => $current_page,
    ));
    $rows = $result['rows'];
    $total = $result['total'];
    $total_pages = max(1, ceil($total / $per_page));
    $list_url = $base_url;
    ?>

    <?php /* Filter — full width, above 2-col body */ ?>
    <div class="wph-fm-card" style="margin-bottom:14px;">
    <form method="get" action="<?php echo admin_url('admin.php'); ?>" id="wph-fm-filter-form">
        <input type="hidden" name="page" value="mb-wphelper-smtp">
        <input type="hidden" name="subtab" value="form-manager">
        <div class="wph-fm-filters">
            <input type="text" name="fm_search" placeholder="<?php esc_attr_e('Tìm kiếm theo tên, email...', 'whp'); ?>" value="<?php echo esc_attr($filter_search); ?>">
            <?php
            /* ── Dropdown state for Form filter ── */
            $form_dd_label = __('Tất cả Form', 'whp');
            $form_dd_dot   = '#dc2626';
            if ( $filter_form_id ) {
                foreach ( $forms_list as $_f ) {
                    if ( $_f->form_id == $filter_form_id ) {
                        $form_dd_label = $_f->form_title ?: $_f->form_id;
                        $form_dd_dot   = '#7c3aed';
                        break;
                    }
                }
            }
            /* ── Dropdown state for Status filter ── */
            $status_dd_cfg = array(
                'new'        => array( '#7c3aed', __('Mới','whp') ),
                'read'       => array( '#2563eb', __('Đã đọc','whp') ),
                'processing' => array( '#d97706', __('Đang xử lý','whp') ),
                'replied'    => array( '#9333ea', __('Khách phản hồi','whp') ),
                'done'       => array( '#16a34a', __('Hoàn thành','whp') ),
                'spam'       => array( '#dc2626', __('Spam','whp') ),
            );
            $status_dd_label = __('Tất cả trạng thái', 'whp');
            $status_dd_dot   = '#dc2626';
            if ( $filter_status && isset( $status_dd_cfg[ $filter_status ] ) ) {
                $status_dd_label = $status_dd_cfg[ $filter_status ][1];
                $status_dd_dot   = $status_dd_cfg[ $filter_status ][0];
            }
            ?>
            <input type="hidden" name="fm_form_id" id="wph-fm-inp-form" value="<?php echo esc_attr($filter_form_id); ?>">
            <div class="wph-fm-dd" id="wph-fm-dd-form">
                <div class="wph-fm-dd-trigger" onclick="wphFmDdToggle('form',this)">
                    <span class="wph-fm-dd-dot" id="wph-fm-dot-form" style="background:<?php echo esc_attr($form_dd_dot); ?>;"></span>
                    <span class="dd-label" id="wph-fm-lbl-form"><?php echo esc_html($form_dd_label); ?></span>
                    <svg class="dd-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-fm-dd-menu" id="wph-fm-menu-form">
                    <div class="wph-fm-dd-opt <?php echo !$filter_form_id ? 'selected' : ''; ?>" data-value="" data-dot="#dc2626" onclick="wphFmDdSelect('form',this)">
                        <span class="wph-fm-dd-dot" style="background:#dc2626;"></span><?php esc_html_e('Tất cả Form', 'whp'); ?>
                    </div>
                    <?php foreach($forms_list as $f): ?>
                    <div class="wph-fm-dd-opt <?php echo $filter_form_id==$f->form_id ? 'selected' : ''; ?>" data-value="<?php echo esc_attr($f->form_id);?>" data-dot="#7c3aed" onclick="wphFmDdSelect('form',this)">
                        <span class="wph-fm-dd-dot" style="background:#7c3aed;"></span><?php echo esc_html($f->form_title?:$f->form_id);?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <input type="hidden" name="fm_status" id="wph-fm-inp-status" value="<?php echo esc_attr($filter_status); ?>">
            <div class="wph-fm-dd" id="wph-fm-dd-status">
                <div class="wph-fm-dd-trigger" onclick="wphFmDdToggle('status',this)">
                    <span class="wph-fm-dd-dot" id="wph-fm-dot-status" style="background:<?php echo esc_attr($status_dd_dot); ?>;"></span>
                    <span class="dd-label" id="wph-fm-lbl-status"><?php echo esc_html($status_dd_label); ?></span>
                    <svg class="dd-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-fm-dd-menu" id="wph-fm-menu-status">
                    <div class="wph-fm-dd-opt <?php echo !$filter_status ? 'selected' : ''; ?>" data-value="" data-dot="#dc2626" onclick="wphFmDdSelect('status',this)">
                        <span class="wph-fm-dd-dot" style="background:#dc2626;"></span><?php esc_html_e('Tất cả trạng thái', 'whp'); ?>
                    </div>
                    <?php foreach($status_dd_cfg as $v=>$sc): ?>
                    <div class="wph-fm-dd-opt <?php echo $filter_status===$v ? 'selected' : ''; ?>" data-value="<?php echo esc_attr($v);?>" data-dot="<?php echo esc_attr($sc[0]);?>" onclick="wphFmDdSelect('status',this)">
                        <span class="wph-fm-dd-dot" style="background:<?php echo esc_attr($sc[0]);?>;"></span><?php echo esc_html($sc[1]);?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
            function _wph_fm_fmt_date($d){ if(!$d) return ''; $p=explode('-',$d); return isset($p[2])?($p[2].'/'.$p[1].'/'.$p[0]):''; }
            $fm_lbl_from = _wph_fm_fmt_date($filter_date_from);
            $fm_lbl_to   = _wph_fm_fmt_date($filter_date_to);
            $fm_lbl      = ($filter_date_from || $filter_date_to)
                ? (($fm_lbl_from ?: '…') . ' – ' . ($fm_lbl_to ?: '…'))
                : __('Tất cả ngày', 'whp');
            ?>
            <div class="wph-fm-daterange-wrap">
                <div class="wph-fm-daterange<?php echo ($filter_date_from||$filter_date_to) ? ' is-active' : ''; ?>" id="fm-daterange-trigger">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span id="fm-date-label"><?php echo esc_html($fm_lbl); ?></span>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="wph-fm-date-popover" id="fm-date-popover">
                    <div class="wph-fm-date-row">
                        <label><?php esc_html_e('Từ ngày', 'whp'); ?></label>
                        <input type="date" name="fm_date_from" id="fm-inp-from" value="<?php echo esc_attr($filter_date_from); ?>">
                    </div>
                    <div class="wph-fm-date-row">
                        <label><?php esc_html_e('Đến ngày', 'whp'); ?></label>
                        <input type="date" name="fm_date_to" id="fm-inp-to" value="<?php echo esc_attr($filter_date_to); ?>">
                    </div>
                    <div class="wph-fm-date-actions">
                        <button type="button" class="wph-fm-date-clear" id="fm-date-clear"><?php esc_html_e('Xóa', 'whp'); ?></button>
                        <button type="submit" class="wph-fm-date-apply"><?php esc_html_e('Áp dụng', 'whp'); ?></button>
                    </div>
                </div>
            </div>
            <button type="submit" style="background:#7c3aed;color:#fff;border:none;border-radius:8px;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;padding:0;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <?php if($filter_search||$filter_status||$filter_form_id||$filter_date_from): ?>
            <a href="<?php echo esc_url($list_url); ?>" style="display:inline-flex;align-items:center;width:36px;height:36px;justify-content:center;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#94a3b8;text-decoration:none;flex-shrink:0;" title="<?php esc_attr_e('Xóa bộ lọc', 'whp'); ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </a>
            <?php endif; ?>
            <button type="button" onclick="wphFmExport('csv')" style="display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:36px;border:1.5px solid #7c3aed;border-radius:8px;background:#f5f3ff;color:#7c3aed;font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap;font-family:inherit;flex-shrink:0;transition:background .15s;" onmouseover="this.style.background='#ede9fe'" onmouseout="this.style.background='#f5f3ff'" title="<?php esc_attr_e('Xuất CSV', 'whp'); ?>">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <?php esc_html_e('Xuất CSV', 'whp'); ?>
            </button>
        </div>
    </form>
    </div>

    <?php /* 2-column body */ ?>
    <div class="wph-fm-body">
    <div> <?php /* ─ Main column ─ */ ?>

    <?php /* Bulk bar */ ?>
    <div class="wph-fm-bulk-bar" id="wph-fm-bulk-bar" style="display:none;">
        <span id="wph-fm-selected-count" style="font-size:13px;font-weight:600;color:#7c3aed;">0 đã chọn</span>
        <input type="hidden" id="wph-fm-bulk-action" value="">
        <div class="wph-fm-dd" id="wph-fm-dd-bulk-action">
            <div class="wph-fm-dd-trigger" onclick="wphFmDdToggle('bulk-action',this)">
                <span class="wph-fm-dd-dot" id="wph-fm-dot-bulk-action" style="background:#94a3b8;"></span>
                <span class="dd-label" id="wph-fm-lbl-bulk-action"><?php esc_html_e('Hành động hàng loạt', 'whp'); ?></span>
                <svg class="dd-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </div>
            <div class="wph-fm-dd-menu" id="wph-fm-menu-bulk-action">
                <div class="wph-fm-dd-opt selected" data-value="" data-dot="#94a3b8" onclick="wphFmBulkDdSelect(this)">
                    <span class="wph-fm-dd-dot" style="background:#94a3b8;"></span><?php esc_html_e('Hành động hàng loạt', 'whp'); ?>
                </div>
                <div class="wph-fm-dd-opt" data-value="read" data-dot="#2563eb" onclick="wphFmBulkDdSelect(this)">
                    <span class="wph-fm-dd-dot" style="background:#2563eb;"></span><?php esc_html_e('Đánh dấu đã đọc', 'whp'); ?>
                </div>
                <div class="wph-fm-dd-opt" data-value="processing" data-dot="#d97706" onclick="wphFmBulkDdSelect(this)">
                    <span class="wph-fm-dd-dot" style="background:#d97706;"></span><?php esc_html_e('Đang xử lý', 'whp'); ?>
                </div>
                <div class="wph-fm-dd-opt" data-value="replied" data-dot="#9333ea" onclick="wphFmBulkDdSelect(this)">
                    <span class="wph-fm-dd-dot" style="background:#9333ea;"></span><?php esc_html_e('Khách phản hồi', 'whp'); ?>
                </div>
                <div class="wph-fm-dd-opt" data-value="done" data-dot="#16a34a" onclick="wphFmBulkDdSelect(this)">
                    <span class="wph-fm-dd-dot" style="background:#16a34a;"></span><?php esc_html_e('Hoàn thành', 'whp'); ?>
                </div>
                <div class="wph-fm-dd-opt" data-value="spam" data-dot="#dc2626" onclick="wphFmBulkDdSelect(this)">
                    <span class="wph-fm-dd-dot" style="background:#dc2626;"></span><?php esc_html_e('Spam', 'whp'); ?>
                </div>
                <div class="wph-fm-dd-opt" data-value="delete" data-dot="#991b1b" onclick="wphFmBulkDdSelect(this)">
                    <span class="wph-fm-dd-dot" style="background:#991b1b;"></span><?php esc_html_e('Xóa', 'whp'); ?>
                </div>
            </div>
        </div>
        <button class="wph-fm-bulk-btn" onclick="wphFmBulkApply()"><?php esc_html_e('Áp dụng', 'whp'); ?></button>
    </div>

    <div class="wph-fm-card">
        <div class="wph-fm-table-wrap">
        <?php if(empty($rows)): ?>
        <div class="wph-fm-empty">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p><?php echo esc_html(($filter_search||$filter_status) ? __('Chưa có liên hệ nào phù hợp bộ lọc.','whp') : __('Chưa có liên hệ nào.','whp')); ?></p>
        </div>
        <?php else: ?>
        <table class="wph-fm-table">
            <thead>
                <tr>
                    <th class="wph-fm-col-check"><input type="checkbox" id="fm-check-all"></th>
                    <th class="wph-fm-col-id">ID</th>
                    <th><?php esc_html_e('Form', 'whp'); ?></th>
                    <th><?php esc_html_e('Họ tên', 'whp'); ?></th>
                    <th><?php esc_html_e('Email', 'whp'); ?></th>
                    <th style="text-align:center;"><?php esc_html_e('Tin nhắn', 'whp'); ?></th>
                    <th><?php esc_html_e('Trạng thái', 'whp'); ?></th>
                    <th><?php esc_html_e('Ngày gửi', 'whp'); ?></th>
                    <th><?php esc_html_e('Thao tác', 'whp'); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($rows as $r): ?>
            <tr class="<?php echo $r->status==='new'?'is-new':($r->status==='replied'?'is-replied':'');?>" id="fm-row-<?php echo $r->id;?>">
                <td class="wph-fm-col-check"><input type="checkbox" class="fm-row-check" value="<?php echo $r->id;?>"></td>
                <td class="wph-fm-col-id">#<?php echo $r->id;?></td>
                <td>
                    <div style="font-weight:600;font-size:13px;color:#0f172a;"><?php echo esc_html($r->form_title?:'Form #'.$r->form_id);?></div>
                    <div style="font-size:11px;color:#94a3b8;"><?php echo esc_html($r->form_plugin);?></div>
                </td>
                <td><?php echo esc_html($r->customer_name?:'—');?></td>
                <td style="color:#2563eb;font-size:12px;"><?php echo esc_html($r->customer_email?:'—');?></td>
                <td style="text-align:center;">
                    <?php $conv_cnt = wph_fm_count_conversations($r->id); ?>
                    <?php if($conv_cnt > 0): ?>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:600;color:#7c3aed;background:#faf5ff;border:1px solid #e9d5ff;padding:2px 9px;border-radius:20px;">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        <?php echo $conv_cnt; ?>
                    </span>
                    <?php else: ?>
                    <span style="color:#cbd5e1;font-size:12px;">—</span>
                    <?php endif; ?>
                </td>
                <td id="fm-badge-<?php echo $r->id;?>"><?php echo wph_fm_status_badge($r->status);?></td>
                <td style="white-space:nowrap;font-size:12px;color:#64748b;"><?php echo date_i18n('d/m/Y H:i',strtotime($r->created_at));?></td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <a href="<?php echo esc_url($base_url.'&fmsub=detail&id='.$r->id);?>" class="wph-fm-btn-icon" title="<?php esc_attr_e('Xem', 'whp'); ?>">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </a>
                        <button class="wph-fm-btn-icon" title="<?php esc_attr_e('Đổi trạng thái', 'whp'); ?>" onclick="wphFmQuickStatus(<?php echo $r->id;?>,this)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="wph-fm-btn-icon red" title="<?php esc_attr_e('Xóa', 'whp'); ?>" onclick="wphFmDeleteOne(<?php echo $r->id;?>)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        </div>

        <?php if($total > 0): ?>
        <div class="wph-fm-pagination">
            <span><?php echo esc_html(sprintf(__('Hiển thị %1$s–%2$s của %3$s', 'whp'), ($current_page-1)*$per_page+1, min($current_page*$per_page,$total), number_format($total))); ?></span>
            <div class="wph-fm-page-links">
                <?php
                $pbase = $list_url.'&fm_status='.$filter_status.'&fm_form_id='.urlencode($filter_form_id).'&fm_date_from='.$filter_date_from.'&fm_date_to='.$filter_date_to.'&fm_search='.urlencode($filter_search);
                // Show first, window around current, last
                $pages_to_show = array();
                for($p=1;$p<=$total_pages;$p++){
                    if($p===1||$p===$total_pages||abs($p-$current_page)<=2) $pages_to_show[$p]=true;
                }
                $prev_p = 0;
                foreach($pages_to_show as $p=>$_){
                    if($prev_p && $p-$prev_p>1) echo '<span class="wph-fm-page-ellipsis">...</span>';
                    if($p===$current_page){
                        echo '<span class="current">'.$p.'</span>';
                    } else {
                        echo '<a href="'.esc_url(add_query_arg('fm_page',$p,$pbase)).'">'.$p.'</a>';
                    }
                    $prev_p=$p;
                }
                ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    </div> <?php /* end main col */ ?>

    <?php /* ─ Sidebar ─ */ ?>
    <div class="wph-fm-sidebar">
        <!-- Quick stats -->
        <div class="wph-fm-sb-card">
            <h4>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <?php esc_html_e('Thống kê nhanh', 'whp'); ?>
            </h4>
            <?php foreach(array(
                __('Liên hệ hôm nay','whp') => $today_count,
                __('Liên hệ 7 ngày','whp')  => $week_count,
                __('Liên hệ 30 ngày','whp') => $month_count,
                __('Chưa xử lý','whp')      => $unread_count,
            ) as $lbl => $val): ?>
            <div class="wph-fm-sb-row">
                <span class="wph-fm-sb-row-lbl"><?php echo esc_html($lbl);?></span>
                <span class="wph-fm-sb-row-val"><?php echo number_format($val);?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Top forms -->
        <?php if(!empty($top_forms)): ?>
        <div class="wph-fm-sb-card">
            <h4>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <?php esc_html_e('Top Form được gửi nhiều', 'whp'); ?>
            </h4>
            <?php $max_cnt = max(1, max(array_column($top_forms,'cnt')));
            foreach($top_forms as $tf): ?>
            <div class="wph-fm-bar-row">
                <span class="wph-fm-bar-name"><?php echo esc_html($tf->form_title?:'Form');?></span>
                <div class="wph-fm-bar-track"><div class="wph-fm-bar-fill" style="width:<?php echo round($tf->cnt/$max_cnt*100);?>%;"></div></div>
                <span class="wph-fm-bar-count"><?php echo number_format($tf->cnt);?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Status breakdown -->
        <div class="wph-fm-sb-card">
            <h4>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?php esc_html_e('Trạng thái', 'whp'); ?>
            </h4>
            <?php $status_cfg = array('new'=>array('#7c3aed',__('Mới','whp')),'read'=>array('#2563eb',__('Đã đọc','whp')),'processing'=>array('#d97706',__('Đang xử lý','whp')),'replied'=>array('#9333ea',__('Khách phản hồi','whp')),'done'=>array('#16a34a',__('Hoàn thành','whp')),'spam'=>array('#dc2626',__('Spam','whp')));
            foreach($status_cfg as $sk=>$sc): ?>
            <div class="wph-fm-sb-row">
                <span class="wph-fm-sb-row-lbl"><span class="wph-fm-status-dot" style="background:<?php echo $sc[0];?>;"></span><?php echo esc_html($sc[1]);?></span>
                <span class="wph-fm-sb-row-val" style="color:<?php echo $sc[0];?>;"><?php echo number_format($sc_map[$sk]??0);?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Export -->
        <div class="wph-fm-sb-card">
            <h4>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <?php esc_html_e('Xuất dữ liệu', 'whp'); ?>
            </h4>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <button onclick="wphFmExport('csv')" style="width:100%;display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px solid #d1fae5;background:linear-gradient(135deg,#f0fdf4,#dcfce7);cursor:pointer;text-align:left;transition:all .15s;" onmouseover="this.style.borderColor='#6ee7b7';this.style.background='linear-gradient(135deg,#dcfce7,#bbf7d0)'" onmouseout="this.style.borderColor='#d1fae5';this.style.background='linear-gradient(135deg,#f0fdf4,#dcfce7)'">
                    <span style="width:32px;height:32px;border-radius:8px;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(22,163,74,.25);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><polyline points="9 15 12 18 15 15"/></svg>
                    </span>
                    <span style="flex:1;">
                        <span style="display:block;font-size:13px;font-weight:700;color:#15803d;"><?php esc_html_e('Xuất CSV', 'whp'); ?></span>
                        <span style="display:block;font-size:11px;color:#4ade80;"><?php esc_html_e('Tương thích mọi phần mềm', 'whp'); ?></span>
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <button onclick="wphFmExport('xlsx')" style="width:100%;display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:1.5px solid #dbeafe;background:linear-gradient(135deg,#eff6ff,#dbeafe);cursor:pointer;text-align:left;transition:all .15s;" onmouseover="this.style.borderColor='#93c5fd';this.style.background='linear-gradient(135deg,#dbeafe,#bfdbfe)'" onmouseout="this.style.borderColor='#dbeafe';this.style.background='linear-gradient(135deg,#eff6ff,#dbeafe)'">
                    <span style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#1d6f42,#1a9e5c);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(29,111,66,.3);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/></svg>
                    </span>
                    <span style="flex:1;">
                        <span style="display:block;font-size:13px;font-weight:700;color:#1e40af;"><?php esc_html_e('Xuất Excel', 'whp'); ?></span>
                        <span style="display:block;font-size:11px;color:#60a5fa;"><?php esc_html_e('Định dạng .xls (SpreadsheetML)', 'whp'); ?></span>
                    </span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>
    </div> <?php /* end .wph-fm-body */ ?>
    <?php endif; /* end list */ ?>
    </div><!-- .wph-fm -->

    <!-- Quick status dropdown -->
    <div id="wph-fm-qs-popup" style="display:none;position:fixed;z-index:99999;background:#fff;border-radius:12px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 10px 30px rgba(0,0,0,.14);border:1px solid #e2e8f0;overflow:hidden;min-width:170px;">
        <div style="padding:8px 12px 6px;border-bottom:1px solid #f1f5f9;">
            <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.6px;"><?php esc_html_e('Đổi trạng thái', 'whp'); ?></span>
        </div>
        <div style="padding:4px;">
        <?php foreach(array(
            'new'        => array('#7c3aed',__('Mới','whp'),            '#f5f3ff'),
            'read'       => array('#2563eb',__('Đã đọc','whp'),         '#eff6ff'),
            'processing' => array('#d97706',__('Đang xử lý','whp'),     '#fffbeb'),
            'replied'    => array('#9333ea',__('Khách phản hồi','whp'), '#fdf4ff'),
            'done'       => array('#16a34a',__('Hoàn thành','whp'),     '#f0fdf4'),
            'spam'       => array('#dc2626',__('Spam','whp'),           '#fef2f2'),
        ) as $v=>$l): ?>
        <div style="padding:7px 10px;cursor:pointer;border-radius:7px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:9px;color:#374151;transition:background .12s;"
             onmouseover="this.style.background='<?php echo $l[2];?>'" onmouseout="this.style.background='transparent'"
             onclick="wphFmSetStatus('<?php echo $v;?>','<?php echo esc_js($l[1]);?>')">
            <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $l[0];?>;flex-shrink:0;box-shadow:0 0 0 2px <?php echo $l[2];?>;"></span>
            <?php echo esc_html($l[1]);?>
        </div>
        <?php endforeach; ?>
        </div>
    </div>

    <script>
    (function(){
        var wphFmI18n = {
            selected:              '<?php echo esc_js( __( 'đã chọn', 'whp' ) ); ?>',
            selectActionError:     '<?php echo esc_js( __( 'Chọn hành động và ít nhất 1 mục', 'whp' ) ); ?>',
            confirmDeleteTitle:    '<?php echo esc_js( __( 'Xác nhận xóa', 'whp' ) ); ?>',
            confirmBulkDeleteMsg:  '<?php echo esc_js( __( 'Bạn có chắc muốn xóa %d liên hệ đã chọn? Hành động này sẽ xóa vĩnh viễn các bản ghi khỏi hệ thống.', 'whp' ) ); ?>',
            confirmSingleDeleteMsg:'<?php echo esc_js( __( 'Bạn có chắc muốn xóa liên hệ #%d? Hành động này sẽ xóa vĩnh viễn bản ghi khỏi hệ thống.', 'whp' ) ); ?>',
            cannotUndo:            '<?php echo esc_js( __( 'Không thể khôi phục sau khi xóa.', 'whp' ) ); ?>',
            cancel:                '<?php echo esc_js( __( 'Hủy', 'whp' ) ); ?>',
            statusChanged:         '<?php echo esc_js( __( 'Đã đổi trạng thái', 'whp' ) ); ?>',
            sending:               '<?php echo esc_js( __( 'Đang gửi...', 'whp' ) ); ?>',
            emptyReply:            '<?php echo esc_js( __( 'Vui lòng nhập nội dung phản hồi', 'whp' ) ); ?>',
            loading:               '<?php echo esc_js( __( 'Đang tải...', 'whp' ) ); ?>',
            loadMore:              '<?php echo esc_js( __( 'Xem thêm', 'whp' ) ); ?>',
            loadFailed:            '<?php echo esc_js( __( 'Tải thất bại', 'whp' ) ); ?>',
            allDates:              '<?php echo esc_js( __( 'Tất cả ngày', 'whp' ) ); ?>'
        };
        var nonce = '<?php echo esc_js($nonce); ?>';
        var ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
        var _qsId = null;

        function post(action, data, cb) {
            var fd = new FormData();
            fd.append('action', action);
            fd.append('nonce', nonce);
            for (var k in data) fd.append(k, data[k]);
            fetch(ajaxUrl, {method:'POST', body:fd})
                .then(function(r){return r.json();})
                .then(cb)
                .catch(function(err){
                    showNotice('Lỗi kết nối: ' + (err.message||err), 'error');
                });
        }

        var _noticeEl = null;
        function showNotice(msg, type, persist) {
            if (!_noticeEl) {
                _noticeEl = document.createElement('div');
                _noticeEl.style.cssText = 'display:none;position:fixed;top:48px;left:50%;transform:translateX(-50%);z-index:999999;min-width:280px;max-width:90%;border-radius:8px;font-size:13px;font-weight:500;padding:10px 14px;box-shadow:0 4px 20px rgba(0,0,0,.18);line-height:1.5;cursor:pointer;';
                _noticeEl.addEventListener('click', function(){ this.style.display = 'none'; });
                document.body.appendChild(_noticeEl);
            }
            var isError = (type === 'error');
            _noticeEl.style.background = isError ? '#fef2f2' : '#f0fdf4';
            _noticeEl.style.color     = isError ? '#dc2626' : '#16a34a';
            _noticeEl.style.border    = isError ? '1px solid #fecaca' : '1px solid #bbf7d0';
            _noticeEl.innerHTML = msg;
            _noticeEl.style.display = 'block';
            if (!persist) {
                setTimeout(function(){ if(_noticeEl) _noticeEl.style.display='none'; }, 5000);
            }
        }
        var _toastEl = null;
        var _toastTimer = null;
        function showToast(msg) {
            if (!_toastEl) {
                _toastEl = document.createElement('div');
                _toastEl.style.cssText = 'display:none;position:fixed;bottom:28px;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;font-size:13px;font-weight:600;padding:10px 22px;border-radius:24px;box-shadow:0 4px 16px rgba(0,0,0,.22);z-index:999999;white-space:nowrap;';
                document.body.appendChild(_toastEl);
            }
            _toastEl.textContent = '✓ ' + msg;
            _toastEl.style.display = 'block';
            if (_toastTimer) clearTimeout(_toastTimer);
            _toastTimer = setTimeout(function(){ if(_toastEl) _toastEl.style.display='none'; }, 5000);
        }

        // Checkbox
        var checkAll = document.getElementById('fm-check-all');
        if (checkAll) {
            checkAll.addEventListener('change', function(){
                document.querySelectorAll('.fm-row-check').forEach(function(c){ c.checked = checkAll.checked; });
                updateBulkBar();
            });
            document.querySelectorAll('.fm-row-check').forEach(function(c){
                c.addEventListener('change', updateBulkBar);
            });
        }
        function updateBulkBar() {
            var checked = document.querySelectorAll('.fm-row-check:checked');
            var bar = document.getElementById('wph-fm-bulk-bar');
            var cnt = document.getElementById('wph-fm-selected-count');
            if (bar) bar.style.display = checked.length > 0 ? 'flex' : 'none';
            if (cnt) cnt.textContent = checked.length + ' ' + wphFmI18n.selected;
        }

        window.wphFmBulkApply = function() {
            var action = document.getElementById('wph-fm-bulk-action').value;
            var ids = Array.from(document.querySelectorAll('.fm-row-check:checked')).map(function(c){ return c.value; });
            if (!action || ids.length === 0) { showNotice(wphFmI18n.selectActionError, 'error'); return; }
            function doBulk() {
                var fd = new FormData();
                fd.append('action','wph_fm_bulk_action'); fd.append('nonce',nonce); fd.append('bulk_action',action);
                ids.forEach(function(id){ fd.append('ids[]', id); });
                fetch(ajaxUrl, {method:'POST',body:fd}).then(function(r){return r.json();}).then(function(res){
                    if (res.success) { showNotice(res.data.message); setTimeout(function(){ location.reload(); }, 800); }
                    else showNotice(res.data.message||'Lỗi','error');
                });
            }
            if (action === 'delete') {
                wphFmShowConfirm({
                    iconBg:   '#fef2f2',
                    iconSvg:  '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg>',
                    title:    wphFmI18n.confirmDeleteTitle,
                    message:  wphFmI18n.confirmBulkDeleteMsg.replace('%d', ids.length),
                    warning:  wphFmI18n.cannotUndo,
                    btnLabel: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg> Xóa ' + ids.length + ' mục',
                    btnStyle: 'background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;box-shadow:0 4px 12px rgba(220,38,38,.3);',
                    onConfirm: doBulk
                });
                return;
            }
            doBulk();
        };

        window._fmConfirmCbs = {};
        window.wphFmShowConfirm = function(opts) {
            var cid = 'fm-confirm-' + Date.now();
            var cbKey = 'cb_' + cid;
            _fmConfirmCbs[cbKey] = opts.onConfirm || function(){};
            var iconBg   = opts.iconBg   || '#fef2f2';
            var iconSvg  = opts.iconSvg  || '';
            var title    = opts.title    || 'Xác nhận';
            var message  = opts.message  || '';
            var warning  = opts.warning  || '';
            var btnLabel = opts.btnLabel || 'Xác nhận';
            var btnStyle = opts.btnStyle || 'background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;box-shadow:0 4px 12px rgba(220,38,38,.3);';
            var h = '<div id="' + cid + '" class="wph-fm-confirm-overlay" onclick="if(event.target===this){document.getElementById(\'' + cid + '\').remove();delete _fmConfirmCbs[\'' + cbKey + '\'];}">';
            h += '<div class="wph-fm-confirm-modal">';
            h += '<div class="wph-fm-confirm-head">';
            h += '<div style="display:flex;align-items:center;gap:12px;">';
            h += '<div style="width:36px;height:36px;border-radius:10px;background:' + iconBg + ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">' + iconSvg + '</div>';
            h += '<h2 style="margin:0;font-size:16px;font-weight:700;color:#1e293b;">' + title + '</h2>';
            h += '</div>';
            h += '<button class="wph-fm-confirm-close" onclick="document.getElementById(\'' + cid + '\').remove()">'
                + '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
                + '</button>';
            h += '</div>';
            h += '<div style="padding:20px 24px;">';
            h += '<div style="font-size:13.5px;color:#475569;line-height:1.7;">' + message + '</div>';
            if (warning) {
                h += '<div style="margin-top:14px;background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:10px 14px;display:flex;align-items:flex-start;gap:8px;">';
                h += '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ca8a04" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                h += '<span style="font-size:12.5px;color:#854d0e;">' + warning + '</span>';
                h += '</div>';
            }
            h += '</div>';
            h += '<div class="wph-fm-confirm-foot">';
            h += '<button onclick="document.getElementById(\'' + cid + '\').remove()" style="padding:9px 22px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;">'+wphFmI18n.cancel+'</button>';
            h += '<button onclick="document.getElementById(\'' + cid + '\').remove();(function(){var f=_fmConfirmCbs[\'' + cbKey + '\'];delete _fmConfirmCbs[\'' + cbKey + '\'];if(f)f();})()" style="padding:9px 22px;border:none;border-radius:8px;' + btnStyle + 'font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;display:inline-flex;align-items:center;gap:6px;">' + btnLabel + '</button>';
            h += '</div>';
            h += '</div></div>';
            document.body.insertAdjacentHTML('beforeend', h);
        };

        window.wphFmDeleteOne = function(id, redirect) {
            wphFmShowConfirm({
                iconBg:   '#fef2f2',
                iconSvg:  '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg>',
                title:    wphFmI18n.confirmDeleteTitle,
                message:  wphFmI18n.confirmSingleDeleteMsg.replace('%d', id),
                warning:  wphFmI18n.cannotUndo,
                btnLabel: '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a1,1,0,0,1,1-1h4a1,1,0,0,1,1,1v2"/></svg> Xóa',
                btnStyle: 'background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;box-shadow:0 4px 12px rgba(220,38,38,.3);',
                onConfirm: function() {
                    post('wph_fm_delete', {id:id}, function(res){
                        if (res.success) {
                            if (redirect) { location.href = redirect; return; }
                            var row = document.getElementById('fm-row-'+id);
                            if (row) row.remove();
                            showNotice('Đã xóa #' + id);
                        } else showNotice(res.data.message||'Lỗi','error');
                    });
                }
            });
        };

        window.wphFmPickStatus = function(el) {
            var picker = document.getElementById('fm-detail-status-picker');
            var hidden = document.getElementById('fm-detail-status');
            if (!picker || !hidden) return;
            var val = el.getAttribute('data-value');
            var dot = el.getAttribute('data-dot');
            var bg  = el.getAttribute('data-bg');
            hidden.value = val;
            Array.from(picker.querySelectorAll('.fm-status-opt')).forEach(function(opt) {
                var oDot = opt.getAttribute('data-dot');
                var oBg  = opt.getAttribute('data-bg');
                var isActive = opt === el;
                opt.style.border = '1.5px solid ' + (isActive ? oDot : '#e2e8f0');
                opt.style.background = isActive ? oBg : '#fff';
                var dotEl   = opt.querySelector('span:first-child');
                var nameEl  = opt.querySelector('span:nth-child(2) > span:first-child');
                var checkEl = opt.querySelector('svg');
                if (dotEl) dotEl.style.boxShadow = isActive
                    ? '0 0 0 3px '+oBg+',0 0 0 4px '+oDot+'33'
                    : '0 0 0 3px #fff,0 0 0 4px '+oDot+'33';
                if (nameEl) { nameEl.style.fontWeight = isActive ? '700' : '500'; nameEl.style.color = isActive ? oDot : '#374151'; }
                if (checkEl && !isActive) checkEl.remove();
            });
            if (!el.querySelector('svg')) {
                var svg = document.createElementNS('http://www.w3.org/2000/svg','svg');
                svg.setAttribute('width','14'); svg.setAttribute('height','14');
                svg.setAttribute('viewBox','0 0 24 24'); svg.setAttribute('fill','none');
                svg.setAttribute('stroke', dot); svg.setAttribute('stroke-width','2.5');
                svg.setAttribute('stroke-linecap','round'); svg.setAttribute('stroke-linejoin','round');
                var pl = document.createElementNS('http://www.w3.org/2000/svg','polyline');
                pl.setAttribute('points','20 6 9 17 4 12'); svg.appendChild(pl);
                el.appendChild(svg);
            }
        };

        window.wphFmSaveStatus = function(id) {
            var status = document.getElementById('fm-detail-status').value;
            post('wph_fm_update_status', {id:id, status:status}, function(res){
                if (res.success) showNotice(res.data.message);
                else showNotice(res.data.message||'Lỗi','error');
            });
        };

        // Quick status from table icon
        window.wphFmQuickStatus = function(id, btn) {
            _qsId = id;
            var popup = document.getElementById('wph-fm-qs-popup');
            var rect = btn.getBoundingClientRect();
            var pw = 170; // min-width of popup
            // position:fixed — dùng viewport coords trực tiếp, không cộng scroll
            var top = rect.bottom + 6;
            var left = rect.right - pw; // căn phải với button
            // tránh tràn sang trái
            if (left < 8) left = 8;
            // tránh tràn xuống đáy viewport
            var ph = 220; // ước tính chiều cao popup
            if (top + ph > window.innerHeight) top = rect.top - ph - 4;
            popup.style.top  = top + 'px';
            popup.style.left = left + 'px';
            popup.style.display = 'block';
            setTimeout(function(){
                document.addEventListener('click', function h(e){
                    if (!popup.contains(e.target)) {
                        popup.style.display = 'none';
                        document.removeEventListener('click', h, true);
                    }
                }, true);
            }, 10);
        };

        window.wphFmSetStatus = function(status, label) {
            if (!_qsId) return;
            document.getElementById('wph-fm-qs-popup').style.display = 'none';
            post('wph_fm_update_status', {id:_qsId, status:status}, function(res){
                if (res.success) {
                    var badge = document.getElementById('fm-badge-'+_qsId);
                    if (badge) badge.innerHTML = res.data.badge;
                    showNotice(wphFmI18n.statusChanged + ' → ' + label);
                } else showNotice(res.data.message||'Lỗi','error');
            });
        };

        window.wphFmSaveSettings = function() {
            var data = {};
            var active = document.getElementById('fm-setting-active');
            if (active) data.active = active.checked ? '1' : '0';
            var retention = document.getElementById('fm-setting-retention');
            if (retention) data.retention = retention.value;
            var maxlogs = document.getElementById('fm-setting-maxlogs');
            if (maxlogs) data.max_logs = maxlogs.value;
            var fd = new FormData();
            fd.append('action','wph_fm_save_settings'); fd.append('nonce',nonce);
            for(var k in data) fd.append(k, data[k]);
            document.querySelectorAll('.fm-plugin-toggle').forEach(function(t){
                fd.append('plugins['+t.dataset.plugin+']', t.checked ? '1' : '0');
            });
            fetch(ajaxUrl, {method:'POST',body:fd}).then(function(r){return r.json();}).then(function(res){
                if(res.success) showNotice(res.data.message);
                else showNotice(res.data.message||'Lỗi','error');
            });
        };

        // ── Custom filter dropdowns ──────────────────────────────────────────────
        function wphFmDdPosition(trigger, menu) {
            var rect = trigger.getBoundingClientRect();
            var mw   = menu.offsetWidth || 180;
            var mh   = menu.offsetHeight || 220;
            var top  = rect.bottom + 4;
            var left = rect.left;
            if (left + mw > window.innerWidth - 8) left = window.innerWidth - mw - 8;
            if (left < 8) left = 8;
            if (top + mh > window.innerHeight) top = rect.top - mh - 4;
            menu.style.top  = top + 'px';
            menu.style.left = left + 'px';
        }

        window.wphFmDdToggle = function(key, triggerEl) {
            var dd      = document.getElementById('wph-fm-dd-' + key);
            var menu    = document.getElementById('wph-fm-menu-' + key);
            var trigger = triggerEl || dd.querySelector('.wph-fm-dd-trigger');
            var isOpen  = dd.classList.contains('open');
            document.querySelectorAll('.wph-fm-dd').forEach(function(d){ d.classList.remove('open'); });
            if (!isOpen) {
                dd.classList.add('open');
                wphFmDdPosition(trigger, menu);
            }
        };

        // Bulk action dropdown — select without auto-submit
        window.wphFmBulkDdSelect = function(el) {
            var val   = el.dataset.value;
            var dot   = el.dataset.dot;
            var label = el.textContent.trim();
            document.getElementById('wph-fm-bulk-action').value = val;
            document.getElementById('wph-fm-dot-bulk-action').style.background = dot;
            document.getElementById('wph-fm-lbl-bulk-action').textContent = label;
            el.closest('.wph-fm-dd-menu').querySelectorAll('.wph-fm-dd-opt').forEach(function(o){ o.classList.remove('selected'); });
            el.classList.add('selected');
            el.closest('.wph-fm-dd').classList.remove('open');
        };

        window.wphFmDdSelect = function(key, el) {
            var val   = el.dataset.value;
            var dot   = el.dataset.dot;
            var label = el.textContent.trim();
            document.getElementById('wph-fm-inp-'  + key).value = val;
            document.getElementById('wph-fm-dot-'  + key).style.background = dot;
            document.getElementById('wph-fm-lbl-'  + key).textContent = label;
            el.closest('.wph-fm-dd-menu').querySelectorAll('.wph-fm-dd-opt').forEach(function(o){ o.classList.remove('selected'); });
            el.classList.add('selected');
            el.closest('.wph-fm-dd').classList.remove('open');
            document.getElementById('wph-fm-filter-form').submit();
        };

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.wph-fm-dd')) {
                document.querySelectorAll('.wph-fm-dd').forEach(function(d){ d.classList.remove('open'); });
            }
        });

        window.wphFmExport = function(format) {
            var status = document.querySelector('[name="fm_status"]') ? document.querySelector('[name="fm_status"]').value : '';
            var formId = document.querySelector('[name="fm_form_id"]') ? document.querySelector('[name="fm_form_id"]').value : '';
            var dateFrom = document.querySelector('[name="fm_date_from"]') ? document.querySelector('[name="fm_date_from"]').value : '';
            var dateTo = document.querySelector('[name="fm_date_to"]') ? document.querySelector('[name="fm_date_to"]').value : '';
            var url = ajaxUrl + '?action=wph_fm_export&nonce=' + encodeURIComponent(nonce)
                + '&format=' + format + '&status=' + status + '&form_id=' + encodeURIComponent(formId)
                + '&date_from=' + dateFrom + '&date_to=' + dateTo;
            window.location.href = url;
        };

        // ── Rich text editor toolbar ──────────────────────────────────────────
        (function() {
            var toolbar = document.getElementById('fm-editor-toolbar');
            var editor  = document.getElementById('fm-conv-editor');
            if (!toolbar || !editor) return;
            toolbar.addEventListener('mousedown', function(e) {
                var btn = e.target.closest('button[data-cmd]');
                if (!btn) return;
                e.preventDefault();
                var cmd = btn.getAttribute('data-cmd');
                if (cmd === 'createLink') {
                    var url = prompt('Nhập URL liên kết:', 'https://');
                    if (url) document.execCommand('createLink', false, url);
                } else {
                    document.execCommand(cmd, false, null);
                }
                editor.focus();
            });
            editor.addEventListener('keyup', function() { updateToolbarState(); });
            editor.addEventListener('mouseup', function() { updateToolbarState(); });
            function updateToolbarState() {
                toolbar.querySelectorAll('button[data-cmd]').forEach(function(btn) {
                    var cmd = btn.getAttribute('data-cmd');
                    if (['bold','italic','underline','insertUnorderedList','insertOrderedList'].indexOf(cmd) > -1) {
                        try { btn.classList.toggle('active', document.queryCommandState(cmd)); } catch(e) {}
                    }
                });
            }
        })();

        window.wphFmSendReply = function(submissionId) {
            var editor = document.getElementById('fm-conv-editor');
            var btn    = document.getElementById('fm-conv-send-btn');
            var content = editor ? editor.innerHTML.trim() : '';
            if (content === '' || content === '<br>') { showNotice(wphFmI18n.emptyReply, 'error'); return; }
            var originalHTML = btn ? btn.innerHTML : '';
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="animation:wph-spin .8s linear infinite;flex-shrink:0;"><circle cx="12" cy="12" r="10" stroke-opacity=".3"/><path d="M12 2a10 10 0 0 1 10 10"/></svg> ' + wphFmI18n.sending;
            }
            post('wph_fm_send_reply', {submission_id: submissionId, content: content}, function(res) {
                if (btn) { btn.disabled = false; btn.innerHTML = originalHTML; }
                if (res.success) {
                    editor.innerHTML = '';
                    var c = res.data.conv;
                    var thread = document.getElementById('fm-conv-thread');
                    var empty = thread.querySelector('p');
                    if (empty) empty.remove();
                    var d = document.createElement('div');
                    d.className = 'fm-conv-msg fm-conv-outbound';
                    d.innerHTML = '<div class="fm-conv-meta">'
                        + '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>'
                        + '<strong>' + (c.author_label||'Admin') + '</strong>'
                        + '<span>' + (c.created_at||'').substring(0,16).replace('T',' ') + '</span>'
                        + '</div><div class="fm-conv-body">' + c.content + '</div>';
                    thread.appendChild(d);
                    if (res.data.sent === false) {
                        var errMsg = res.data.mail_error ? res.data.mail_error : 'Không rõ lỗi — kiểm tra cài đặt SMTP.';
                        var logLink = res.data.log_url ? ' <a href="' + res.data.log_url + '" target="_blank" style="color:#dc2626;text-decoration:underline">Xem Email Log</a>' : '';
                        showNotice('&#9888; Đã lưu nhưng gửi email thất bại: <strong>' + errMsg + '</strong>' + logLink, 'error', true);
                    } else {
                        showToast(res.data.message || 'Đã gửi phản hồi thành công');
                    }
                    d.scrollIntoView({behavior:'smooth', block:'nearest'});
                } else {
                    showNotice(res.data && res.data.message ? res.data.message : 'Gửi thất bại', 'error');
                }
            });
        };

        window.wphFmLoadMoreConv = function(btn) {
            var submissionId = btn.getAttribute('data-submission-id');
            var offset = parseInt(btn.getAttribute('data-offset'), 10);
            btn.disabled = true;
            btn.textContent = wphFmI18n.loading;
            post('wph_fm_load_more_conv', {submission_id: submissionId, offset: offset}, function(res) {
                btn.disabled = false;
                if (!res.success) { btn.textContent = wphFmI18n.loadMore; showNotice(wphFmI18n.loadFailed, 'error'); return; }
                var items = res.data.items || [];
                var thread = document.getElementById('fm-conv-thread');
                var emptyP = thread.querySelector('p');
                if (emptyP) emptyP.remove();
                var frag = document.createDocumentFragment();
                items.forEach(function(c) {
                    var d = document.createElement('div');
                    d.className = 'fm-conv-msg fm-conv-' + c.direction;
                    var icon = c.direction === 'outbound'
                        ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>'
                        : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9,17 4,12 9,7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>';
                    d.innerHTML = '<div class="fm-conv-meta">' + icon
                        + '<strong>' + (c.author_label || '') + '</strong>'
                        + '<span>' + (c.created_at || '').replace('T', ' ').substring(0, 16) + '</span>'
                        + '</div><div class="fm-conv-body">' + c.content + '</div>';
                    frag.appendChild(d);
                });
                thread.insertBefore(frag, thread.firstChild);
                if (!res.data.has_more) {
                    var wrap = document.getElementById('fm-conv-load-more-wrap');
                    if (wrap) wrap.remove();
                } else {
                    btn.setAttribute('data-offset', res.data.next_offset);
                    btn.textContent = 'Xem thêm';
                }
            });
        };

        // ── Date range popover ──────────────────────────────────────────
        (function(){
            var trigger = document.getElementById('fm-daterange-trigger');
            var popover = document.getElementById('fm-date-popover');
            if (!trigger || !popover) return;
            function fmReposPopover(){
                if (!popover.classList.contains('open')) return;
                var rect = trigger.getBoundingClientRect();
                var pw = 240;
                var left = rect.right - pw;
                if (left < 8) left = 8;
                if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
                var adminBar = document.getElementById('wpadminbar');
                var minTop = adminBar ? adminBar.offsetHeight + 4 : 36;
                var top = rect.bottom + 7;
                if (top < minTop) top = minTop;
                popover.style.left = left + 'px';
                popover.style.top  = top + 'px';
            }
            trigger.addEventListener('click', function(e){
                e.stopPropagation();
                var isOpen = popover.classList.contains('open');
                popover.classList.toggle('open', !isOpen);
                trigger.classList.toggle('open', !isOpen);
                if (!isOpen) fmReposPopover();
            });
            document.addEventListener('click', function(e){
                if (!trigger.closest('.wph-fm-daterange-wrap').contains(e.target)) {
                    popover.classList.remove('open');
                    trigger.classList.remove('open');
                }
            });
            document.addEventListener('scroll', fmReposPopover, { passive: true, capture: true });
            var clearBtn = document.getElementById('fm-date-clear');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(){
                    document.getElementById('fm-inp-from').value = '';
                    document.getElementById('fm-inp-to').value = '';
                    document.getElementById('wph-fm-filter-form').submit();
                });
            }
            var inpFrom = document.getElementById('fm-inp-from');
            var inpTo   = document.getElementById('fm-inp-to');
            var label   = document.getElementById('fm-date-label');
            function updateLabel(){
                var f = inpFrom ? inpFrom.value : '', t = inpTo ? inpTo.value : '';
                if (!f && !t) { label.textContent = wphFmI18n.allDates; return; }
                function fmt(d){ if(!d) return ''; var p=d.split('-'); return p[2]+'/'+p[1]+'/'+p[0]; }
                label.textContent = (f ? fmt(f) : '…') + ' – ' + (t ? fmt(t) : '…');
            }
            if (inpFrom) inpFrom.addEventListener('change', updateLabel);
            if (inpTo)   inpTo.addEventListener('change', updateLabel);
        })();
    })();
    </script>
    <?php
}
