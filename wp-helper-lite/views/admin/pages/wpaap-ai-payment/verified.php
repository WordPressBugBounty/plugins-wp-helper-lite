<?php defined('ABSPATH') || exit;

function wpaap_aipay_verified_layout() {
    $nonce = wp_create_nonce('wpaap_vf_nonce');
    ?>
<style>
/* ── Layout ─────────────────────────────────────── */
.vf2-root { display:grid; grid-template-columns:1fr 260px; gap:18px; align-items:start; }
.vf2-main { min-width:0; }
.vf2-side { display:flex; flex-direction:column; gap:14px; }
#vf2-table-wrap { overflow-x:auto; }
@media(max-width:960px){
    .vf2-root { grid-template-columns:1fr; }
}

/* ── Toolbar ─────────────────────────────────────── */
.vf2-toolbar { display:flex; align-items:center; gap:8px; margin-bottom:14px; width:100%; }
.vf2-search-wrap { position:relative; flex:1; min-width:0; }
.vf2-search-wrap input[type=text] { width:100%; height:36px; padding:0 10px 0 34px;
    border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#0f172a;
    background:#fff; outline:none; box-sizing:border-box; }
.vf2-search-wrap input[type=text]:focus { border-color:#3b82f6; box-shadow:0 0 0 2px rgba(59,130,246,.12); }
.vf2-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none; }
.vf2-btn { height:36px; padding:0 14px; border-radius:8px; font-size:13px; font-weight:600;
           border:none; cursor:pointer; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; }
.vf2-btn-primary { background:#3b82f6; color:#fff; }
.vf2-btn-primary:hover { background:#2563eb; }
.vf2-btn-icon { height:36px; width:36px; border-radius:8px; background:#f1f5f9; color:#475569;
                border:1px solid #e2e8f0; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; }
.vf2-btn-icon:hover { background:#e2e8f0; }

/* ── Date range picker (identical style to risk tab) ── */
.vf2-dr-wrap { position:relative; }
.vf2-dr-trigger { display:inline-flex; align-items:center; gap:6px; height:36px; padding:0 11px;
    border:1px solid #e2e8f0; border-radius:8px; background:#fff; font-size:12px; color:#374151;
    cursor:pointer; white-space:nowrap; transition:border-color .15s,color .15s; }
.vf2-dr-trigger:hover,.vf2-dr-trigger.open { border-color:#3b82f6; color:#3b82f6; }
.vf2-dr-popover { display:none; position:absolute; top:calc(100% + 7px); right:0; z-index:9999;
    background:#fff; border:1px solid #e2e8f0; border-radius:12px;
    box-shadow:0 8px 28px rgba(0,0,0,.13); padding:14px 16px; min-width:230px; }
.vf2-dr-popover.open { display:block; }
.vf2-dr-row { display:flex; flex-direction:column; gap:4px; margin-bottom:10px; }
.vf2-dr-row label { font-size:10.5px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.05em; }
.vf2-dr-row input[type="date"] { width:100%; height:32px; border:1px solid #e2e8f0 !important;
    border-radius:7px !important; padding:0 8px !important; font-size:12px !important;
    color:#374151 !important; background:#f8fafc !important; box-shadow:none !important; cursor:pointer; box-sizing:border-box; }
.vf2-dr-row input[type="date"]:focus { border-color:#3b82f6 !important; outline:none !important; }
.vf2-dr-actions { display:flex; gap:7px; margin-top:6px; }
.vf2-dr-apply { flex:1; padding:7px 12px; border:none; border-radius:7px; background:#3b82f6;
    color:#fff; font-size:12px; font-weight:600; cursor:pointer; }
.vf2-dr-apply:hover { background:#2563eb; }
.vf2-dr-clear { padding:7px 10px; border:1px solid #e2e8f0; border-radius:7px; background:#fff;
    color:#64748b; font-size:12px; cursor:pointer; }
.vf2-dr-clear:hover { background:#f8fafc; }

/* ── Table card ─────────────────────────────────── */
.vf2-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; overflow:hidden;
            box-shadow:0 1px 4px rgba(0,0,0,.04); }
.vf2-tbl { width:100%; border-collapse:collapse; font-size:13px; }
.vf2-tbl thead th { padding:10px 14px; background:#f8fafc; border-bottom:1px solid #e2e8f0;
                    font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;
                    letter-spacing:.05em; text-align:left; white-space:nowrap; }
.vf2-tbl tbody td { padding:11px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.vf2-tbl tbody tr:last-child td { border-bottom:none; }
.vf2-tbl tbody tr:hover td { background:#fafafa; }
.vf2-oid { font-weight:700; color:#0ea5e9; font-size:12.5px; text-decoration:none; }
.vf2-oid:hover { color:#0284c7; }
.vf2-email { font-size:11.5px; color:#94a3b8; }
.vf2-name { font-weight:500; color:#0f172a; }
.vf2-amt { font-weight:700; color:#0f172a; }
.vf2-bank { color:#475569; font-size:12.5px; }
.vf2-date { color:#64748b; font-size:12px; white-space:nowrap; }
.vf2-badge-ok { display:inline-flex; align-items:center; gap:4px; padding:3px 9px;
                border-radius:20px; font-size:10.5px; font-weight:700;
                background:#d1fae5; color:#065f46; white-space:nowrap; }
.vf2-conf-hi { color:#16a34a; font-weight:700; }
.vf2-conf-md { color:#d97706; font-weight:700; }
.vf2-conf-lo { color:#dc2626; font-weight:700; }
.vf2-act-btn { padding:4px 8px; border-radius:6px; background:#f1f5f9; border:1px solid #e2e8f0;
               color:#475569; cursor:pointer; font-size:12px; text-decoration:none;
               display:inline-flex; align-items:center; gap:4px; }
.vf2-act-btn:hover { background:#e2e8f0; }

/* ── Pagination ─────────────────────────────────── */
.vf2-pag { display:flex; align-items:center; gap:6px; padding:12px 16px;
           border-top:1px solid #f1f5f9; flex-wrap:wrap; }
.vf2-pag-info { font-size:12px; color:#64748b; margin-right:auto; }
.vf2-pag-btn { min-width:32px; height:32px; padding:0 8px; border-radius:6px; font-size:12px;
               font-weight:600; border:1px solid #e2e8f0; background:#fff; color:#475569;
               cursor:pointer; }
.vf2-pag-btn.active { background:#3b82f6; color:#fff; border-color:#3b82f6; }
.vf2-pag-btn:hover:not(.active):not(:disabled) { background:#f1f5f9; }
.vf2-pag-btn:disabled { opacity:.4; cursor:default; }
.vf2-per { height:32px; padding:0 8px; border:1px solid #e2e8f0; border-radius:6px;
           font-size:12px; color:#475569; background:#fff; cursor:pointer; margin-left:8px; }

/* ── Empty / Loading ─────────────────────────────── */
.vf2-empty { text-align:center; padding:64px 20px; }
.vf2-empty-icon { font-size:40px; margin-bottom:12px; }
.vf2-empty h3 { font-size:15px; font-weight:700; color:#0f172a; margin:0 0 6px; }
.vf2-empty p { font-size:13px; color:#64748b; margin:0; }
.vf2-loading { display:flex; align-items:center; justify-content:center; padding:60px 20px; gap:10px; color:#64748b; font-size:13px; }
.vf2-spinner { width:18px; height:18px; border:2px solid #e2e8f0; border-top-color:#3b82f6;
               border-radius:50%; animation:vf2-spin .7s linear infinite; }
@keyframes vf2-spin { to { transform:rotate(360deg); } }

/* ── Sidebar cards ──────────────────────────────── */
.vf2-scard { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:16px;
             box-shadow:0 1px 4px rgba(0,0,0,.04); }
.vf2-scard-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.vf2-scard-title { font-size:13px; font-weight:700; color:#0f172a; }
.vf2-range-sel { display:flex; gap:0; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden; }
.vf2-range-btn { padding:4px 9px; font-size:11px; font-weight:600; color:#64748b;
                 background:#fff; border:none; cursor:pointer; }
.vf2-range-btn.active { background:#3b82f6; color:#fff; }
.vf2-kpi-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
.vf2-kpi { background:#f8fafc; border-radius:10px; padding:10px 12px; }
.vf2-kpi-val { font-size:18px; font-weight:800; color:#0f172a; line-height:1.1; }
.vf2-kpi-val.green { color:#16a34a; }
.vf2-kpi-lbl { font-size:10.5px; color:#64748b; margin-top:2px; }
.vf2-spark-wrap { position:relative; height:52px; margin-top:4px; }
.vf2-spark-wrap canvas { display:block; width:100%; height:100%; }

.vf2-bank-row { display:flex; align-items:center; gap:8px; margin-bottom:10px; font-size:12px; }
.vf2-bank-name { width:64px; color:#475569; font-weight:600; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex-shrink:0; }
.vf2-bank-bar-wrap { flex:1; background:#f1f5f9; border-radius:20px; height:7px; overflow:hidden; }
.vf2-bank-bar { height:100%; border-radius:20px; }
.vf2-bank-pct { width:32px; text-align:right; color:#0f172a; font-weight:700; }

.vf2-donut-wrap { display:flex; flex-direction:column; align-items:center; gap:10px; }
.vf2-donut-wrap canvas { display:block; }
.vf2-donut-legend { width:100%; }
.vf2-legend-row { display:flex; align-items:center; gap:8px; font-size:12px; margin-bottom:6px; }
.vf2-legend-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.vf2-legend-name { flex:1; color:#475569; }
.vf2-legend-val { font-weight:700; color:#0f172a; }
</style>

<div class="vf2-root">

    <!-- ── MAIN COLUMN ── -->
    <div class="vf2-main">

        <!-- Toolbar -->
        <div class="vf2-toolbar">
            <div class="vf2-search-wrap">
                <svg class="vf2-search-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="vf2-search" placeholder="<?php echo esc_attr__( 'Tìm đơn hàng (#ID hoặc tên)…', 'whp' ); ?>" />
            </div>
            <!-- Date range picker (same style as risk tab) -->
            <div class="vf2-dr-wrap">
                <div class="vf2-dr-trigger" id="vf2-dr-trigger">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span id="vf2-dr-label"><?php esc_html_e( 'Tất cả ngày', 'whp' ); ?></span>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="vf2-dr-popover" id="vf2-dr-popover">
                    <div class="vf2-dr-row">
                        <label><?php esc_html_e( 'Từ ngày', 'whp' ); ?></label>
                        <input type="date" id="vf2-inp-from">
                    </div>
                    <div class="vf2-dr-row">
                        <label><?php esc_html_e( 'Đến ngày', 'whp' ); ?></label>
                        <input type="date" id="vf2-inp-to">
                    </div>
                    <div class="vf2-dr-actions">
                        <button type="button" class="vf2-dr-clear" id="vf2-dr-clear"><?php esc_html_e( 'Xóa', 'whp' ); ?></button>
                        <button type="button" class="vf2-dr-apply" id="vf2-dr-apply"><?php esc_html_e( 'Áp dụng', 'whp' ); ?></button>
                    </div>
                </div>
            </div>
            <button class="vf2-btn vf2-btn-primary" id="vf2-filter-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
                <?php esc_html_e( 'Bộ lọc', 'whp' ); ?>
            </button>
            <button class="vf2-btn-icon" id="vf2-refresh-btn" title="<?php esc_attr_e( 'Làm mới', 'whp' ); ?>">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
            </button>
        </div>

        <!-- Table card -->
        <div class="vf2-card">
            <div id="vf2-table-wrap">
                <div class="vf2-loading">
                    <div class="vf2-spinner"></div> <?php esc_html_e( 'Đang tải…', 'whp' ); ?>
                </div>
            </div>
            <div id="vf2-pag-wrap"></div>
        </div>

    </div><!-- /.vf2-main -->

    <!-- ── SIDEBAR ── -->
    <div class="vf2-side">

        <!-- Tổng quan -->
        <div class="vf2-scard">
            <div class="vf2-scard-hd">
                <span class="vf2-scard-title"><?php esc_html_e( 'Tổng quan', 'whp' ); ?></span>
                <div class="vf2-range-sel">
                    <button class="vf2-range-btn active" data-range="today"><?php esc_html_e( 'Hôm nay', 'whp' ); ?></button>
                    <button class="vf2-range-btn" data-range="7d"><?php esc_html_e( '7 ngày', 'whp' ); ?></button>
                    <button class="vf2-range-btn" data-range="30d"><?php esc_html_e( '30 ngày', 'whp' ); ?></button>
                </div>
            </div>
            <div class="vf2-kpi-grid" id="vf2-kpi-grid">
                <div class="vf2-kpi"><div class="vf2-kpi-val" id="vf2-k-count">—</div><div class="vf2-kpi-lbl"><?php esc_html_e( 'Giao dịch', 'whp' ); ?></div></div>
                <div class="vf2-kpi"><div class="vf2-kpi-val green" id="vf2-k-amt">—</div><div class="vf2-kpi-lbl"><?php esc_html_e( 'Tổng tiền', 'whp' ); ?></div></div>
                <div class="vf2-kpi"><div class="vf2-kpi-val" id="vf2-k-rate">—</div><div class="vf2-kpi-lbl"><?php esc_html_e( 'Tỉ lệ tự động', 'whp' ); ?></div></div>
                <div class="vf2-kpi"><div class="vf2-kpi-val" id="vf2-k-time">—</div><div class="vf2-kpi-lbl"><?php esc_html_e( 'Thời gian XM', 'whp' ); ?></div></div>
            </div>
            <div class="vf2-spark-wrap">
                <canvas id="vf2-spark" height="52"></canvas>
            </div>
        </div>

        <!-- Theo ngân hàng -->
        <div class="vf2-scard">
            <div class="vf2-scard-hd">
                <span class="vf2-scard-title"><?php esc_html_e( 'Theo ngân hàng', 'whp' ); ?></span>
            </div>
            <div id="vf2-banks-wrap">
                <div style="font-size:12px;color:#94a3b8;text-align:center;padding:20px 0;"><?php esc_html_e( 'Đang tải…', 'whp' ); ?></div>
            </div>
        </div>

        <!-- Nguồn xác minh -->
        <div class="vf2-scard">
            <div class="vf2-scard-hd">
                <span class="vf2-scard-title"><?php esc_html_e( 'Nguồn xác minh', 'whp' ); ?></span>
            </div>
            <div class="vf2-donut-wrap">
                <canvas id="vf2-donut" width="120" height="120"></canvas>
                <div class="vf2-donut-legend" id="vf2-donut-legend">
                    <div style="font-size:12px;color:#94a3b8;text-align:center;padding:10px 0;">Đang tải…</div>
                </div>
            </div>
        </div>

    </div><!-- /.vf2-side -->
</div><!-- /.vf2-root -->

<script>
var whpI18n = <?php echo wp_json_encode([
    'billion'        => __( ' tỷ', 'whp' ),
    'million'        => __( ' tr', 'whp' ),
    'seconds'        => __( 'giây', 'whp' ),
    'minutes'        => __( ' phút', 'whp' ),
    'loading'        => __( 'Đang tải…', 'whp' ),
    'loadDataFailed' => __( 'Không tải được dữ liệu', 'whp' ),
    'pleaseTryAgain' => __( 'Vui lòng thử lại.', 'whp' ),
    'noTransactions' => __( 'Không có giao dịch nào', 'whp' ),
    'tryChangeFilter'=> __( 'Thử thay đổi bộ lọc hoặc khoảng thời gian.', 'whp' ),
    'order'          => __( 'Đơn hàng', 'whp' ),
    'customer'       => __( 'Khách hàng', 'whp' ),
    'amount'         => __( 'Số tiền', 'whp' ),
    'bank'           => __( 'Ngân hàng', 'whp' ),
    'verifyTime'     => __( 'Thời gian xác minh', 'whp' ),
    'confidence'     => __( 'Độ tin cậy', 'whp' ),
    'statusCol'      => __( 'Trạng thái', 'whp' ),
    'actions'        => __( 'Thao tác', 'whp' ),
    'verifySuccess'  => __( '✓ Xác minh thành công', 'whp' ),
    'orders'         => __( 'đơn hàng', 'whp' ),
    'noData'         => __( 'Chưa có dữ liệu', 'whp' ),
    'from'           => __( 'Từ', 'whp' ),
    'to'             => __( 'Đến', 'whp' ),
    'allDates'       => __( 'Tất cả ngày', 'whp' ),
]); ?>;
(function(){
    var ajaxUrl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
    var nonce   = '<?php echo esc_js($nonce); ?>';
    var BANK_COLORS = ['#3b82f6','#10b981','#ec4899','#8b5cf6','#f59e0b','#94a3b8'];
    var PROV_COLORS = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#94a3b8'];

    var state = { page:1, per:20, q:'', from:'', to:'' };

    // ── helpers ──────────────────────────────────────────────────
    function post(action, data, cb) {
        var fd = new FormData();
        fd.append('action', action);
        fd.append('nonce', nonce);
        Object.keys(data).forEach(function(k){ fd.append(k, data[k]); });
        fetch(ajaxUrl, {method:'POST', body:fd})
            .then(function(r){ return r.json(); })
            .then(cb)
            .catch(function(){ cb({success:false}); });
    }

    function fmt_vnd(n) {
        if (n >= 1e9) return (n/1e9).toFixed(1).replace(/\.0$/,'') + whpI18n.billion;
        if (n >= 1e6) return (n/1e6).toFixed(1).replace(/\.0$/,'') + whpI18n.million;
        return Number(n).toLocaleString('vi') + 'đ';
    }
    function fmt_sec(s) {
        if (!s) return '—';
        if (s < 60) return s + whpI18n.seconds;
        return Math.round(s/60) + whpI18n.minutes;
    }
    function conf_class(c) {
        if (c >= 80) return 'vf2-conf-hi';
        if (c >= 50) return 'vf2-conf-md';
        return 'vf2-conf-lo';
    }

    // ── TABLE ─────────────────────────────────────────────────────
    function loadTable() {
        var tw = document.getElementById('vf2-table-wrap');
        tw.innerHTML = '<div class="vf2-loading"><div class="vf2-spinner"></div> ' + whpI18n.loading + '</div>';
        document.getElementById('vf2-pag-wrap').innerHTML = '';

        post('wpaap_vf_list', state, function(res) {
            if (!res.success || !res.data) {
                tw.innerHTML = '<div class="vf2-empty"><div class="vf2-empty-icon">⚠️</div><h3>' + whpI18n.loadDataFailed + '</h3><p>' + whpI18n.pleaseTryAgain + '</p></div>';
                return;
            }
            var d = res.data;
            if (!d.rows || !d.rows.length) {
                tw.innerHTML = '<div class="vf2-empty"><div class="vf2-empty-icon">🔍</div><h3>' + whpI18n.noTransactions + '</h3><p>' + whpI18n.tryChangeFilter + '</p></div>';
                return;
            }

            var html = '<table class="vf2-tbl"><thead><tr>'
                + '<th>' + whpI18n.order + '</th><th>' + whpI18n.customer + '</th><th>' + whpI18n.amount + '</th>'
                + '<th>' + whpI18n.bank + '</th><th>' + whpI18n.verifyTime + '</th>'
                + '<th>' + whpI18n.confidence + '</th><th>' + whpI18n.statusCol + '</th><th>' + whpI18n.actions + '</th>'
                + '</tr></thead><tbody>';

            d.rows.forEach(function(r) {
                var cc = conf_class(r.confidence);
                html += '<tr>'
                    + '<td><a class="vf2-oid" href="' + r.edit_url + '">#' + r.id + '</a>'
                    + (r.email ? '<div class="vf2-email">' + r.email + '</div>' : '') + '</td>'
                    + '<td><div class="vf2-name">' + r.name + '</div></td>'
                    + '<td><div class="vf2-amt">' + r.amount + 'đ</div></td>'
                    + '<td><div class="vf2-bank">' + r.bank + '</div></td>'
                    + '<td><div class="vf2-date">' + r.ver_at + '</div></td>'
                    + '<td><span class="' + cc + '">' + r.confidence + '%</span></td>'
                    + '<td><span class="vf2-badge-ok">' + whpI18n.verifySuccess + '</span></td>'
                    + '<td><a class="vf2-act-btn" href="' + r.edit_url + '" target="_blank">'
                    + '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'
                    + '</a></td>'
                    + '</tr>';
            });
            html += '</tbody></table>';
            tw.innerHTML = html;

            renderPag(d.total, d.pages, d.page);
        });
    }

    function renderPag(total, pages, cur) {
        var wrap = document.getElementById('vf2-pag-wrap');
        if (pages <= 1 && total === 0) { wrap.innerHTML = ''; return; }

        var from = (cur-1)*state.per + 1;
        var to   = Math.min(cur*state.per, total);

        var html = '<div class="vf2-pag">'
            + '<span class="vf2-pag-info">' + from + '–' + to + ' / ' + total + ' ' + whpI18n.orders + '</span>';

        html += '<button class="vf2-pag-btn" data-p="prev" ' + (cur<=1?'disabled':'') + '>‹</button>';

        var nums = pagNums(cur, pages);
        nums.forEach(function(n) {
            if (n === '…') {
                html += '<span style="padding:0 4px;color:#94a3b8;font-size:12px;">…</span>';
            } else {
                html += '<button class="vf2-pag-btn' + (n===cur?' active':'') + '" data-p="' + n + '">' + n + '</button>';
            }
        });

        html += '<button class="vf2-pag-btn" data-p="next" ' + (cur>=pages?'disabled':'') + '>›</button>';
        html += '</div>';
        wrap.innerHTML = html;

        wrap.querySelectorAll('.vf2-pag-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var p = this.dataset.p;
                if (p==='prev') state.page = Math.max(1, cur-1);
                else if (p==='next') state.page = Math.min(pages, cur+1);
                else state.page = parseInt(p);
                loadTable();
            });
        });
    }

    function pagNums(cur, total) {
        if (total <= 7) {
            var a = []; for(var i=1;i<=total;i++) a.push(i); return a;
        }
        var nums = [1];
        if (cur > 3) nums.push('…');
        for (var i=Math.max(2,cur-1); i<=Math.min(total-1,cur+1); i++) nums.push(i);
        if (cur < total-2) nums.push('…');
        nums.push(total);
        return nums;
    }

    // ── STATS ─────────────────────────────────────────────────────
    var statsRange = 'today';
    function loadStats() {
        post('wpaap_vf_stats', {range: statsRange}, function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;

            // KPIs
            document.getElementById('vf2-k-count').textContent = d.count.toLocaleString('vi');
            document.getElementById('vf2-k-amt').textContent   = fmt_vnd(d.amount);
            document.getElementById('vf2-k-rate').textContent  = d.count ? '100%' : '—';
            document.getElementById('vf2-k-time').textContent  = fmt_sec(d.avg_time);

            // Sparkline
            drawSparkline(d.hourly || []);

            // Banks
            var bw = document.getElementById('vf2-banks-wrap');
            if (!d.banks || !d.banks.length) {
                bw.innerHTML = '<div style="font-size:12px;color:#94a3b8;text-align:center;padding:20px 0;">' + whpI18n.noData + '</div>';
            } else {
                var bh = '';
                d.banks.forEach(function(b, i) {
                    var col = BANK_COLORS[i % BANK_COLORS.length];
                    bh += '<div class="vf2-bank-row">'
                        + '<div class="vf2-bank-name" title="'+b.name+'">'+b.name+'</div>'
                        + '<div class="vf2-bank-bar-wrap"><div class="vf2-bank-bar" style="width:'+b.pct+'%;background:'+col+'"></div></div>'
                        + '<div class="vf2-bank-pct">'+b.pct+'%</div>'
                        + '</div>';
                });
                bw.innerHTML = bh;
            }

            // Donut
            var dl = document.getElementById('vf2-donut-legend');
            if (!d.providers || !d.providers.length) {
                dl.innerHTML = '<div style="font-size:12px;color:#94a3b8;text-align:center;padding:10px 0;">' + whpI18n.noData + '</div>';
                drawDonut([], 0);
            } else {
                drawDonut(d.providers, d.count);
                var lh = '';
                d.providers.forEach(function(p, i) {
                    var col = PROV_COLORS[i % PROV_COLORS.length];
                    lh += '<div class="vf2-legend-row">'
                        + '<div class="vf2-legend-dot" style="background:'+col+'"></div>'
                        + '<div class="vf2-legend-name">'+p.name+'</div>'
                        + '<div class="vf2-legend-val">'+p.pct+'%</div>'
                        + '</div>';
                });
                dl.innerHTML = lh;
            }
        });
    }

    function drawSparkline(data) {
        var canvas = document.getElementById('vf2-spark');
        if (!canvas || !canvas.getContext) return;
        var dpr = window.devicePixelRatio || 1;
        var W = canvas.parentElement.offsetWidth || 248;
        var H = 52;
        canvas.width  = W * dpr;
        canvas.height = H * dpr;
        canvas.style.width  = W + 'px';
        canvas.style.height = H + 'px';
        var ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);

        var max = Math.max.apply(null, data) || 1;
        var pad = {l:4, r:4, t:6, b:4};
        var cw = W - pad.l - pad.r;
        var ch = H - pad.t - pad.b;
        var n  = data.length;

        function px(i) { return pad.l + (i / (n-1)) * cw; }
        function py(v) { return pad.t + ch - (v / max) * ch; }

        // gradient fill
        var grad = ctx.createLinearGradient(0, pad.t, 0, H);
        grad.addColorStop(0, 'rgba(16,185,129,.25)');
        grad.addColorStop(1, 'rgba(16,185,129,0)');

        ctx.beginPath();
        ctx.moveTo(px(0), py(data[0]));
        for (var i=1; i<n; i++) {
            var cx1 = px(i-1) + (px(i)-px(i-1))/2;
            ctx.bezierCurveTo(cx1, py(data[i-1]), cx1, py(data[i]), px(i), py(data[i]));
        }
        ctx.lineTo(px(n-1), H);
        ctx.lineTo(px(0), H);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();

        // line
        ctx.beginPath();
        ctx.moveTo(px(0), py(data[0]));
        for (var j=1; j<n; j++) {
            var cx2 = px(j-1) + (px(j)-px(j-1))/2;
            ctx.bezierCurveTo(cx2, py(data[j-1]), cx2, py(data[j]), px(j), py(data[j]));
        }
        ctx.strokeStyle = '#10b981';
        ctx.lineWidth   = 2;
        ctx.stroke();
    }

    function drawDonut(providers, total) {
        var canvas = document.getElementById('vf2-donut');
        if (!canvas || !canvas.getContext) return;
        var dpr = window.devicePixelRatio || 1;
        canvas.width  = 120 * dpr;
        canvas.height = 120 * dpr;
        var ctx = canvas.getContext('2d');
        ctx.scale(dpr, dpr);
        var cx = 60, cy = 60, R = 50, r = 32;

        if (!providers.length || !total) {
            ctx.beginPath();
            ctx.arc(cx, cy, R, 0, 2*Math.PI);
            ctx.fillStyle = '#f1f5f9';
            ctx.fill();
            return;
        }

        var start = -Math.PI/2;
        providers.forEach(function(p, i) {
            var sweep = (p.count / total) * 2 * Math.PI;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, R, start, start+sweep);
            ctx.closePath();
            ctx.fillStyle = PROV_COLORS[i % PROV_COLORS.length];
            ctx.fill();
            start += sweep;
        });

        // white inner circle (donut hole)
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, 2*Math.PI);
        ctx.fillStyle = '#fff';
        ctx.fill();

        // center label
        ctx.fillStyle = '#0f172a';
        ctx.font = 'bold 16px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(total, cx, cy);
    }

    // ── DATE RANGE PICKER ─────────────────────────────────────────
    var drTrigger = document.getElementById('vf2-dr-trigger');
    var drPopover = document.getElementById('vf2-dr-popover');
    var drLabel   = document.getElementById('vf2-dr-label');
    var drFrom    = document.getElementById('vf2-inp-from');
    var drTo      = document.getElementById('vf2-inp-to');

    function drFmtDate(val) {
        if (!val) return '';
        var p = val.split('-'); // yyyy-mm-dd
        return p[2] + '/' + p[1] + '/' + p[0];
    }
    function drUpdateLabel() {
        var f = drFrom.value, t = drTo.value;
        if (f && t) drLabel.textContent = drFmtDate(f) + ' – ' + drFmtDate(t);
        else if (f)  drLabel.textContent = whpI18n.from + ' ' + drFmtDate(f);
        else if (t)  drLabel.textContent = whpI18n.to + ' ' + drFmtDate(t);
        else         drLabel.textContent = whpI18n.allDates;
        drTrigger.classList.toggle('open', !!(f || t));
    }

    drTrigger.addEventListener('click', function(e) {
        e.stopPropagation();
        drPopover.classList.toggle('open');
        drTrigger.classList.toggle('open', drPopover.classList.contains('open'));
    });

    document.getElementById('vf2-dr-clear').addEventListener('click', function() {
        drFrom.value = ''; drTo.value = '';
        state.from = ''; state.to = '';
        drUpdateLabel();
        drPopover.classList.remove('open');
        drTrigger.classList.remove('open');
        state.page = 1; loadTable();
    });

    document.getElementById('vf2-dr-apply').addEventListener('click', function() {
        state.from = drFrom.value;
        state.to   = drTo.value;
        drUpdateLabel();
        drPopover.classList.remove('open');
        state.page = 1; loadTable();
    });

    document.addEventListener('click', function(e) {
        if (!drTrigger.closest('.vf2-dr-wrap').contains(e.target)) {
            drPopover.classList.remove('open');
            drUpdateLabel();
        }
    });

    // ── EVENTS ────────────────────────────────────────────────────
    document.getElementById('vf2-filter-btn').addEventListener('click', function() {
        state.q    = document.getElementById('vf2-search').value.trim();
        state.page = 1;
        loadTable();
    });

    document.getElementById('vf2-refresh-btn').addEventListener('click', function() {
        loadTable();
        loadStats();
    });

    document.getElementById('vf2-search').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') document.getElementById('vf2-filter-btn').click();
    });

    document.querySelectorAll('.vf2-range-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.vf2-range-btn').forEach(function(b){ b.classList.remove('active'); });
            this.classList.add('active');
            statsRange = this.dataset.range;
            loadStats();
        });
    });

    // ── INIT ──────────────────────────────────────────────────────
    loadTable();
    loadStats();
})();
</script>
<?php
}
