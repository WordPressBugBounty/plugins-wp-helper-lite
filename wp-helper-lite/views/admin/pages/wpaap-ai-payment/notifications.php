<?php defined('ABSPATH') || exit;

function wpaap_aipay_notifications_hero() { ?>
<style>
.wpaap-notif-hero {
    background-color: #fff1f2;
    background-image:
        linear-gradient(110deg, rgba(255,255,255,.97) 0%, rgba(255,241,242,.94) 40%, rgba(255,228,230,.9) 100%),
        radial-gradient(circle, #fda4af 1.5px, transparent 1.5px);
    background-size: auto, 22px 22px;
    border-radius: 18px; border: 1px solid #fecdd3;
    box-shadow: 0 4px 20px rgba(225,29,72,0.10);
    padding: 24px 28px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 20px;
    overflow: hidden; position: relative; min-height: 88px;
}
.wpaap-notif-hero-icon {
    width: 50px; height: 50px; border-radius: 14px;
    background: linear-gradient(135deg, #e11d48, #be123c);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(225,29,72,0.35); flex-shrink: 0; position: relative; z-index: 1;
}
.wpaap-notif-hero-text { position: relative; z-index: 1; }
.wpaap-notif-hero-text h2 { margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #881337; }
.wpaap-notif-hero-text p  { margin: 0; font-size: 13px; color: #9f1239; line-height: 1.6; }
.wpaap-notif-hero-deco {
    position: absolute; right: 0; top: 0; bottom: 0; width: 380px;
    pointer-events: none; overflow: hidden;
}
.wpaap-notif-hero-deco svg { display: block; width: 100%; height: 100%; overflow: hidden; }
</style>
<div class="wpaap-notif-hero">
    <div class="wpaap-notif-hero-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
    </div>
    <div class="wpaap-notif-hero-text">
        <h2><?php esc_html_e('Thông báo đa kênh', 'whp'); ?></h2>
        <p><?php esc_html_e('Cấu hình kênh nhận thông báo đơn hàng, xác nhận chuyển khoản và cảnh báo rủi ro từ AI Thanh Toán.', 'whp'); ?></p>
    </div>
    <div class="wpaap-notif-hero-deco" aria-hidden="true">
        <svg viewBox="0 0 380 88" fill="none" xmlns="http://www.w3.org/2000/svg" overflow="hidden">
            <defs>
                <linearGradient id="nhFade" x1="0" y1="0" x2="1" y2="0">
                    <stop offset="0%" stop-color="#fff1f2" stop-opacity="1"/>
                    <stop offset="70%" stop-color="#fff1f2" stop-opacity="0"/>
                </linearGradient>
            </defs>
            <!-- Concentric rings -->
            <circle cx="300" cy="44" r="54" fill="rgba(225,29,72,0.05)"/>
            <circle cx="300" cy="44" r="38" fill="rgba(225,29,72,0.07)"/>
            <circle cx="300" cy="44" r="24" fill="rgba(225,29,72,0.09)"/>
            <!-- Bell -->
            <path d="M300 22 C287 22 276 31 276 43 C276 55 268 60 268 60 L332 60 C332 60 324 55 324 43 C324 31 313 22 300 22Z" fill="rgba(225,29,72,0.18)" stroke="rgba(225,29,72,0.35)" stroke-width="1.5"/>
            <path d="M293 60 C293 63.3 296.1 66 300 66 C303.9 66 307 63.3 307 60Z" fill="rgba(225,29,72,0.3)"/>
            <!-- Check badge -->
            <circle cx="320" cy="26" r="13" fill="#e11d48"/>
            <polyline points="313,26 318,31 327,21" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            <!-- Notification pill: Email -->
            <rect x="140" y="14" width="88" height="26" rx="13" fill="#fff" fill-opacity="0.92" stroke="#fecdd3" stroke-width="1"/>
            <circle cx="157" cy="27" r="7" fill="#22c55e" fill-opacity="0.85"/>
            <rect x="170" y="22" width="44" height="5" rx="2.5" fill="#fda4af"/>
            <rect x="170" y="31" width="30" height="3.5" rx="1.75" fill="#fecdd3"/>
            <!-- Notification pill: Discord -->
            <rect x="148" y="52" width="88" height="26" rx="13" fill="#fff" fill-opacity="0.92" stroke="#fecdd3" stroke-width="1"/>
            <circle cx="165" cy="65" r="7" fill="#6366f1" fill-opacity="0.8"/>
            <rect x="178" y="60" width="44" height="5" rx="2.5" fill="#c7d2fe"/>
            <rect x="178" y="69" width="32" height="3.5" rx="1.75" fill="#e0e7ff"/>
            <!-- Fade -->
            <rect x="0" y="0" width="110" height="88" fill="url(#nhFade)"/>
        </svg>
    </div>
</div>
<?php }

function wpaap_aipay_notifications_layout()
{
    $saved = false;
    if (isset($_POST['whp_aipay_notif_save'])) {
        if (!isset($_POST['whp_aipay_notif_nonce']) || !wp_verify_nonce($_POST['whp_aipay_notif_nonce'], 'whp_aipay_notifications_save')) {
            wp_die('Security check failed.');
        }
        if (!current_user_can('manage_options')) wp_die(esc_html__('Không có quyền.', 'whp'));
        whp_save_aipay_settings([
            'whp_aipay_email_enable', 'whp_aipay_email_address',
            'whp_aipay_discord_enable', 'whp_aipay_discord_webhook',
            'whp_aipay_webhook_enable', 'whp_aipay_webhook_url', 'whp_aipay_webhook_method',
        ]);
        $saved = true;
    }

    $email_enable    = whp_get_setting('whp_aipay_email_enable')    ?? '';
    $email_address   = whp_get_setting('whp_aipay_email_address')   ?? '';
    $discord_enable  = whp_get_setting('whp_aipay_discord_enable')  ?? '';
    $discord_webhook = whp_get_setting('whp_aipay_discord_webhook') ?? '';
    $webhook_enable  = whp_get_setting('whp_aipay_webhook_enable')  ?? '';
    $webhook_url     = whp_get_setting('whp_aipay_webhook_url')     ?? '';
    $webhook_method  = whp_get_setting('whp_aipay_webhook_method')  ?? 'POST';
    ?>
<style>
/* ── Notifications Layout ─────────────────────────────────── */
.wpaap-notif-layout {
    display: grid; grid-template-columns: 1fr 300px; gap: 20px; align-items: start;
}
.wpaap-notif-sidebar { position: sticky; top: 40px; display: flex; flex-direction: column; gap: 14px; }

/* ── Section headers ──────────────────────────────────────── */
.wpaap-nc-section { margin-bottom: 0; }
.wpaap-nc-section + .wpaap-nc-section { margin-top: 24px; }
.wpaap-nc-section-title {
    font-size: 11.5px; font-weight: 700; color: #64748b;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 0 0 10px; padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: 8px;
}

/* ── Channel cards ───────────────────────────────────────── */
.wpaap-nc {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    margin-bottom: 10px; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,.04); transition: border-color .15s, box-shadow .15s;
}
.wpaap-nc:last-child { margin-bottom: 0; }
.wpaap-nc:not(.wpaap-nc-locked):hover { border-color: #fda4af; box-shadow: 0 2px 8px rgba(225,29,72,.07); }
input.wpaap-nc-toggle[type="checkbox"] { display: none !important; }

/* Head */
.wpaap-nc-head {
    display: flex; align-items: center; gap: 12px;
    padding: 14px 16px; background: #fff;
    border-bottom: 1px solid transparent; transition: border-color .15s, background .15s;
}
.wpaap-nc-toggle:checked ~ .wpaap-nc-head { border-bottom-color: #f1f5f9; background: #fefefe; }

/* Icon */
.wpaap-nc-icon {
    width: 40px; height: 40px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.wpaap-nc-icon.email   { background: #ecfdf5; }
.wpaap-nc-icon.discord { background: #eef2ff; }
.wpaap-nc-icon.webhook { background: #f1f5f9; }

/* Info */
.wpaap-nc-info { flex: 1; min-width: 0; }
.wpaap-nc-name {
    font-size: 14px; font-weight: 700; color: #0f172a;
    display: flex; align-items: center; gap: 7px; margin-bottom: 2px;
}
.wpaap-nc-badge { font-size: 10px; font-weight: 700; padding: 1px 8px; border-radius: 20px; background: #f1f5f9; color: #64748b; }
.wpaap-nc-badge.soon { background: #fef9c3; color: #a16207; }
.wpaap-nc-badge.free { background: #dcfce7; color: #15803d; }
.wpaap-nc-desc { font-size: 12px; color: #64748b; }

/* Toggle visual */
.wpaap-nc-toggle-vis {
    display: inline-block; width: 38px; height: 22px; border-radius: 22px;
    background: #d1d5db; position: relative; cursor: pointer;
    flex-shrink: 0; transition: background .2s;
}
.wpaap-nc-toggle-vis::after {
    content: ''; position: absolute; width: 16px; height: 16px;
    border-radius: 50%; background: #fff; top: 3px; left: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,.2); transition: transform .2s;
}
.wpaap-nc-toggle:checked ~ .wpaap-nc-head .wpaap-nc-toggle-vis { background: #22c55e; }
.wpaap-nc-toggle:checked ~ .wpaap-nc-head .wpaap-nc-toggle-vis::after { transform: translateX(16px); }

/* Chevron */
.wpaap-nc-chevron { color: #cbd5e1; transition: transform .25s; flex-shrink: 0; cursor: pointer; }
.wpaap-nc-toggle:checked ~ .wpaap-nc-head .wpaap-nc-chevron { transform: rotate(180deg); color: #94a3b8; }

/* Stats row */
.wpaap-nc-stats {
    display: flex; align-items: center;
    padding: 10px 16px; background: #fafafa; border-bottom: 1px solid #f1f5f9;
}
.wpaap-nc-stat-col {
    flex: 1; text-align: center; padding: 0 8px; border-right: 1px solid #f1f5f9;
}
.wpaap-nc-stat-col:last-of-type { border-right: none; }
.wpaap-nc-stat-val { font-size: 15px; font-weight: 700; color: #0f172a; display: block; }
.wpaap-nc-stat-lbl { font-size: 10.5px; color: #94a3b8; font-weight: 500; }
.wpaap-nc-cfg-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 7px; cursor: pointer;
    background: #f8fafc; border: 1px solid #e2e8f0;
    font-size: 12px; font-weight: 600; color: #64748b;
    margin-left: auto; white-space: nowrap; flex-shrink: 0;
    transition: background .15s, border-color .15s, color .15s;
}
.wpaap-nc-cfg-btn:hover { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.wpaap-nc-toggle:checked ~ .wpaap-nc-stats .wpaap-nc-cfg-btn { background: #fff1f2; border-color: #fecdd3; color: #be123c; }

/* Body (accordion) */
.wpaap-nc-body { display: none; padding: 18px 18px 20px; }
.wpaap-nc-toggle:checked ~ .wpaap-nc-body { display: block; }

/* Locked */
.wpaap-nc-locked { opacity: .6; }
.wpaap-nc-locked .wpaap-nc-head { cursor: default; }

/* ── Form fields ──────────────────────────────────────────── */
.wpaap-nf-row { margin-bottom: 14px; }
.wpaap-nf-row:last-child { margin-bottom: 0; }
.wpaap-nf-label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 5px; }
.wpaap-nf-input {
    width: 100%; padding: 9px 12px; border: 1px solid #d1d5db !important; border-radius: 8px !important;
    font-size: 13.5px; color: #0f172a; background: #fff; box-sizing: border-box;
    outline: none; box-shadow: none !important; transition: border-color .15s, box-shadow .15s;
}
.wpaap-nf-input:focus { border-color: #e11d48 !important; box-shadow: 0 0 0 3px rgba(225,29,72,.1) !important; }
.wpaap-nf-select,
.wp-core-ui select.wpaap-nf-select {
    border: 1px solid #d1d5db !important; border-radius: 8px !important;
    font-size: 13.5px; color: #0f172a; background: #fff;
    outline: none; min-width: 120px; cursor: pointer; box-shadow: none !important; transition: border-color .15s;
}
.wpaap-nf-hint {
    margin-top: 8px; padding: 10px 12px;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;
    display: flex; gap: 9px; align-items: flex-start;
}
.wpaap-nf-hint-icon { flex-shrink: 0; margin-top: 1px; color: #94a3b8; }
.wpaap-nf-hint-body { font-size: 12px; color: #64748b; line-height: 1.6; }
.wpaap-nf-hint-body strong { color: #374151; font-weight: 700; display: block; margin-bottom: 3px; }
.wpaap-nf-hint-steps { margin: 4px 0 0; padding-left: 0; list-style: none; display: flex; flex-direction: column; gap: 2px; }
.wpaap-nf-hint-steps li { display: flex; gap: 6px; align-items: baseline; font-size: 11.5px; }
.wpaap-nf-hint-steps li::before { content: attr(data-n); font-weight: 700; color: #e11d48; flex-shrink: 0; font-size: 10px; }
.wpaap-nf-select:focus,
.wp-core-ui select.wpaap-nf-select:focus { border-color: #e11d48 !important; box-shadow: 0 0 0 3px rgba(225,29,72,.1) !important; }
.wpaap-nf-divider { height: 1px; background: #f1f5f9; margin: 14px 0; }
.wpaap-nf-payload-lbl { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 6px; }
.wpaap-nf-payload { background: #0f172a; border-radius: 10px; padding: 14px 16px; overflow-x: auto; }
.wpaap-nf-payload pre { margin: 0; font-family: 'Cascadia Code','Fira Mono','Consolas',monospace; font-size: 12px; line-height: 1.65; color: #e2e8f0; }
.wpaap-nf-payload .pk { color: #93c5fd; }
.wpaap-nf-payload .ps { color: #86efac; }
.wpaap-nf-payload .pn { color: #fde68a; }
.wpaap-nf-payload .pb { color: #f9a8d4; }
.wpaap-nf-payload .pz { color: #94a3b8; }

/* ── Sidebar cards ────────────────────────────────────────── */
.wpaap-nsc { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.04); }
.wpaap-nsc-hd { display: flex; align-items: center; gap: 9px; padding: 12px 14px; border-bottom: 1px solid #f1f5f9; background: #fafafa; }
.wpaap-nsc-hd-icon { width: 26px; height: 26px; border-radius: 7px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.wpaap-nsc-hd-icon.rose  { background: #fff1f2; }
.wpaap-nsc-hd-icon.blue  { background: #eff6ff; }
.wpaap-nsc-hd-icon.amber { background: #fffbeb; }
.wpaap-nsc-hd-icon.green { background: #f0fdf4; }
.wpaap-nsc-title { font-size: 12.5px; font-weight: 700; color: #0f172a; flex: 1; }
.wpaap-nsc-body { padding: 13px 14px; }

/* Steps */
.wpaap-ns-steps { display: flex; flex-direction: column; gap: 11px; }
.wpaap-ns-step  { display: flex; gap: 10px; align-items: flex-start; }
.wpaap-ns-step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: #e11d48; color: #fff; font-size: 11px; font-weight: 700;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
}
.wpaap-ns-step-title { font-size: 12.5px; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
.wpaap-ns-step-desc  { font-size: 11.5px; color: #64748b; line-height: 1.5; }

/* Status */
.wpaap-ns-status-row {
    display: flex; align-items: center; gap: 9px;
    padding: 7px 0; border-bottom: 1px solid #f8fafc; font-size: 12.5px;
}
.wpaap-ns-status-row:last-child { border-bottom: none; padding-bottom: 0; }
.wpaap-ns-status-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.wpaap-ns-status-dot.on  { background: #22c55e; }
.wpaap-ns-status-dot.off { background: #d1d5db; }
.wpaap-ns-status-name { flex: 1; color: #334155; font-weight: 500; }
.wpaap-ns-status-tag { font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 20px; }
.wpaap-ns-status-tag.on  { background: #dcfce7; color: #15803d; }
.wpaap-ns-status-tag.off { background: #f1f5f9; color: #94a3b8; }

/* Tips */
.wpaap-ns-tips { display: flex; flex-direction: column; gap: 8px; }
.wpaap-ns-tip {
    display: flex; gap: 9px; align-items: flex-start;
    padding: 8px 10px; background: #f8fafc; border-radius: 8px; border: 1px solid #f1f5f9;
}
.wpaap-ns-tip-icon { font-size: 14px; line-height: 1; flex-shrink: 0; margin-top: 1px; }
.wpaap-ns-tip-text { font-size: 11.5px; color: #475569; line-height: 1.55; }
.wpaap-ns-tip-text strong { color: #0f172a; font-weight: 700; }
.wpaap-ns-tips-more {
    display: block; text-align: center; margin-top: 8px;
    padding: 6px; border: 1px dashed #e2e8f0; border-radius: 7px;
    font-size: 11.5px; color: #94a3b8; text-decoration: none;
    transition: border-color .15s, color .15s;
    cursor: pointer;
}
.wpaap-ns-tips-more:hover { border-color: #fda4af; color: #e11d48; }

/* Quick stats */
.wpaap-ns-qrow {
    display: flex; align-items: center; justify-content: space-between;
    padding: 7px 0; border-bottom: 1px solid #f8fafc; font-size: 12.5px;
}
.wpaap-ns-qrow:last-child { border-bottom: none; }
.wpaap-ns-qrow-lbl { color: #64748b; }
.wpaap-ns-qrow-val { font-weight: 700; color: #0f172a; }
.wpaap-ns-qrow-val.ok { color: #e11d48; }

/* Save notice */
.wpaap-notif-saved {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 10px; margin-bottom: 16px; font-size: 13px; color: #166534; font-weight: 600;
}

@media (max-width: 960px) {
    .wpaap-notif-layout { grid-template-columns: 1fr; }
    .wpaap-notif-sidebar { position: static; }
}
</style>

<?php if ($saved): ?>
<div class="wpaap-notif-saved">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    <?php esc_html_e('Đã lưu cài đặt thông báo thành công.', 'whp'); ?>
</div>
<?php endif; ?>

<div class="wpaap-notif-layout">

<!-- ══ LEFT: Form ════════════════════════════════════════════ -->
<div>
<form method="post" action="" id="wpaap-notif-form">
<?php wp_nonce_field('whp_aipay_notifications_save', 'whp_aipay_notif_nonce'); ?>

<!-- ── Section: Danh sách kênh ──────────────────────────── -->
<div class="wpaap-nc-section">
    <!-- EMAIL ------------------------------------------------ -->
    <div class="wpaap-nc">
        <input type="checkbox" id="nc-email" name="whp_aipay_email_enable" value="1" class="wpaap-nc-toggle" <?php checked($email_enable, '1'); ?>>
        <div class="wpaap-nc-head">
            <div class="wpaap-nc-icon email">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
                </svg>
            </div>
            <div class="wpaap-nc-info">
                <div class="wpaap-nc-name">Email <span class="wpaap-nc-badge free"><?php esc_html_e('Miễn phí', 'whp'); ?></span></div>
                <div class="wpaap-nc-desc"><?php esc_html_e('Gửi thông báo đến hộp thư của admin', 'whp'); ?></div>
            </div>
            <label for="nc-email" class="wpaap-nc-toggle-vis" title="<?php esc_attr_e('Bật/tắt kênh Email', 'whp'); ?>"></label>
            <label for="nc-email" class="wpaap-nc-chevron">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </label>
        </div>
        <div class="wpaap-nc-stats">
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">—</span><span class="wpaap-nc-stat-lbl"><?php esc_html_e('Đã gửi hôm nay', 'whp'); ?></span></div>
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">—</span><span class="wpaap-nc-stat-lbl"><?php esc_html_e('Tổng tháng này', 'whp'); ?></span></div>
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">100%</span><span class="wpaap-nc-stat-lbl"><?php esc_html_e('Tỷ lệ thành công', 'whp'); ?></span></div>
            <label for="nc-email" class="wpaap-nc-cfg-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <?php esc_html_e('Cấu hình', 'whp'); ?>
            </label>
        </div>
        <div class="wpaap-nc-body">
            <div class="wpaap-nf-row">
                <label class="wpaap-nf-label" for="whp_aipay_email_address"><?php esc_html_e('Địa chỉ email nhận thông báo', 'whp'); ?></label>
                <input type="text" id="whp_aipay_email_address" name="whp_aipay_email_address" class="wpaap-nf-input" value="<?php echo esc_attr($email_address); ?>" placeholder="admin@yoursite.com">
            </div>
        </div>
    </div>

    <!-- DISCORD ---------------------------------------------- -->
    <div class="wpaap-nc">
        <input type="checkbox" id="nc-discord" name="whp_aipay_discord_enable" value="1" class="wpaap-nc-toggle" <?php checked($discord_enable, '1'); ?>>
        <div class="wpaap-nc-head">
            <div class="wpaap-nc-icon discord">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#6366f1">
                    <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                </svg>
            </div>
            <div class="wpaap-nc-info">
                <div class="wpaap-nc-name">Discord <span class="wpaap-nc-badge free"><?php esc_html_e('Miễn phí', 'whp'); ?></span></div>
                <div class="wpaap-nc-desc"><?php esc_html_e('Đẩy thông báo vào kênh Discord qua Webhook', 'whp'); ?></div>
            </div>
            <label for="nc-discord" class="wpaap-nc-toggle-vis" title="<?php esc_attr_e('Bật/tắt kênh Discord', 'whp'); ?>"></label>
            <label for="nc-discord" class="wpaap-nc-chevron">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </label>
        </div>
        <div class="wpaap-nc-stats">
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">—</span><span class="wpaap-nc-stat-lbl"><?php esc_html_e('Tin nhắn hôm nay', 'whp'); ?></span></div>
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">—</span><span class="wpaap-nc-stat-lbl"><?php esc_html_e('Tổng tháng này', 'whp'); ?></span></div>
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">100%</span><span class="wpaap-nc-stat-lbl">Webhook uptime</span></div>
            <label for="nc-discord" class="wpaap-nc-cfg-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <?php esc_html_e('Cấu hình', 'whp'); ?>
            </label>
        </div>
        <div class="wpaap-nc-body">
            <div class="wpaap-nf-row">
                <label class="wpaap-nf-label" for="whp_aipay_discord_webhook">Webhook URL</label>
                <input type="text" id="whp_aipay_discord_webhook" name="whp_aipay_discord_webhook" class="wpaap-nf-input" value="<?php echo esc_attr($discord_webhook); ?>" placeholder="https://discord.com/api/webhooks/123456/...">
                <div class="wpaap-nf-hint">
                    <div class="wpaap-nf-hint-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <div class="wpaap-nf-hint-body">
                        <strong><?php esc_html_e('Cách lấy Discord Webhook URL', 'whp'); ?></strong>
                        <ol class="wpaap-nf-hint-steps">
                            <li data-n="1."><?php esc_html_e('Mở Discord → vào kênh muốn nhận thông báo → ⚙️', 'whp'); ?> <em><?php esc_html_e('Cài đặt kênh', 'whp'); ?></em></li>
                            <li data-n="2."><?php esc_html_e('Chọn', 'whp'); ?> <em><?php esc_html_e('Tích hợp', 'whp'); ?></em> → <em>Webhooks</em> → <em>New Webhook</em></li>
                            <li data-n="3."><?php esc_html_e('Đặt tên bot, chọn kênh → nhấn', 'whp'); ?> <em>Copy Webhook URL</em> <?php esc_html_e('rồi dán vào đây', 'whp'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WEBHOOK ---------------------------------------------- -->
    <div class="wpaap-nc">
        <input type="checkbox" id="nc-webhook" name="whp_aipay_webhook_enable" value="1" class="wpaap-nc-toggle" <?php checked($webhook_enable, '1'); ?>>
        <div class="wpaap-nc-head">
            <div class="wpaap-nc-icon webhook">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                    <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                </svg>
            </div>
            <div class="wpaap-nc-info">
                <div class="wpaap-nc-name"><?php esc_html_e('Webhook tùy chỉnh', 'whp'); ?></div>
                <div class="wpaap-nc-desc"><?php esc_html_e('Gửi payload JSON đến server hoặc service của bạn', 'whp'); ?></div>
            </div>
            <label for="nc-webhook" class="wpaap-nc-toggle-vis" title="<?php esc_attr_e('Bật/tắt Webhook', 'whp'); ?>"></label>
            <label for="nc-webhook" class="wpaap-nc-chevron">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </label>
        </div>
        <div class="wpaap-nc-stats">
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">—</span><span class="wpaap-nc-stat-lbl"><?php esc_html_e('Requests hôm nay', 'whp'); ?></span></div>
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">—</span><span class="wpaap-nc-stat-lbl"><?php esc_html_e('Tổng tháng này', 'whp'); ?></span></div>
            <div class="wpaap-nc-stat-col"><span class="wpaap-nc-stat-val">—</span><span class="wpaap-nc-stat-lbl">Avg response</span></div>
            <label for="nc-webhook" class="wpaap-nc-cfg-btn">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 00-2 2v.18a2 2 0 01-1 1.73l-.43.25a2 2 0 01-2 0l-.15-.08a2 2 0 00-2.73.73l-.22.38a2 2 0 00.73 2.73l.15.1a2 2 0 011 1.72v.51a2 2 0 01-1 1.74l-.15.09a2 2 0 00-.73 2.73l.22.38a2 2 0 002.73.73l.15-.08a2 2 0 012 0l.43.25a2 2 0 011 1.73V20a2 2 0 002 2h.44a2 2 0 002-2v-.18a2 2 0 011-1.73l.43-.25a2 2 0 012 0l.15.08a2 2 0 002.73-.73l.22-.39a2 2 0 00-.73-2.73l-.15-.08a2 2 0 01-1-1.74v-.5a2 2 0 011-1.74l.15-.09a2 2 0 00.73-2.73l-.22-.38a2 2 0 00-2.73-.73l-.15.08a2 2 0 01-2 0l-.43-.25a2 2 0 01-1-1.73V4a2 2 0 00-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <?php esc_html_e('Cấu hình', 'whp'); ?>
            </label>
        </div>
        <div class="wpaap-nc-body">
            <div class="wpaap-nf-row">
                <label class="wpaap-nf-label" for="whp_aipay_webhook_url">Endpoint URL</label>
                <input type="text" id="whp_aipay_webhook_url" name="whp_aipay_webhook_url" class="wpaap-nf-input" value="<?php echo esc_attr($webhook_url); ?>" placeholder="https://your-server.com/webhook">
                <div class="wpaap-nf-hint">
                    <div class="wpaap-nf-hint-icon">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <div class="wpaap-nf-hint-body">
                        <strong><?php esc_html_e('Cách hoạt động', 'whp'); ?></strong>
                        <ol class="wpaap-nf-hint-steps">
                            <li data-n="1."><?php esc_html_e('WP Helper gửi POST request kèm JSON payload đến URL này sau mỗi sự kiện thanh toán', 'whp'); ?></li>
                            <li data-n="2."><?php esc_html_e('Server của bạn cần trả về HTTP', 'whp'); ?> <strong>200</strong> <?php esc_html_e('để xác nhận đã nhận thành công', 'whp'); ?></li>
                            <li data-n="3."><?php esc_html_e('Có thể dùng Make, Zapier, n8n hoặc endpoint tự viết để xử lý dữ liệu', 'whp'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="wpaap-nf-row">
                <label class="wpaap-nf-label" for="whp_aipay_webhook_method"><?php esc_html_e('Phương thức HTTP', 'whp'); ?></label>
                <select id="whp_aipay_webhook_method" name="whp_aipay_webhook_method" class="wpaap-nf-select">
                    <option value="POST" <?php selected($webhook_method, 'POST'); ?>>POST</option>
                    <option value="GET"  <?php selected($webhook_method, 'GET');  ?>>GET</option>
                </select>
            </div>
            <div class="wpaap-nf-divider"></div>
            <div class="wpaap-nf-payload-lbl"><?php esc_html_e('Xem trước JSON payload', 'whp'); ?></div>
            <div class="wpaap-nf-payload">
                <pre>{
  <span class="pk">"event"</span>:       <span class="ps">"payment.verified"</span>,
  <span class="pk">"timestamp"</span>:   <span class="ps">"2026-06-16T10:30:00+07:00"</span>,
  <span class="pk">"site_url"</span>:    <span class="ps">"https://yoursite.com"</span>,
  <span class="pk">"order"</span>: {
    <span class="pk">"id"</span>:          <span class="pn">1042</span>,
    <span class="pk">"status"</span>:      <span class="ps">"processing"</span>,
    <span class="pk">"total"</span>:       <span class="pn">350000</span>,
    <span class="pk">"currency"</span>:    <span class="ps">"VND"</span>,
    <span class="pk">"customer"</span>:    <span class="ps">"Nguyen Van A"</span>
  },
  <span class="pk">"ai_verdict"</span>: {
    <span class="pk">"risk_score"</span>:  <span class="pn">0.04</span>,
    <span class="pk">"verified"</span>:    <span class="pb">true</span>,
    <span class="pk">"tampered"</span>:    <span class="pb">false</span>,
    <span class="pk">"note"</span>:        <span class="pz">null</span>
  }
}</pre>
            </div>
        </div>
    </div>

</div><!-- /section channels -->

</form>
</div><!-- /left col -->

<!-- ══ RIGHT: Sidebar ════════════════════════════════════════ -->
<aside class="wpaap-notif-sidebar">

    <!-- 3. Mẹo sử dụng -->
    <div class="wpaap-nsc">
        <div class="wpaap-nsc-hd">
            <div class="wpaap-nsc-hd-icon amber">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <span class="wpaap-nsc-title"><?php esc_html_e('Mẹo sử dụng', 'whp'); ?></span>
        </div>
        <div class="wpaap-nsc-body">
            <div class="wpaap-ns-tips">
                <div class="wpaap-ns-tip">
                    <div class="wpaap-ns-tip-icon">&#x1F4A1;</div>
                    <div class="wpaap-ns-tip-text"><strong><?php esc_html_e('Bật cảnh báo rủi ro', 'whp'); ?></strong> <?php esc_html_e('để nhận ngay khi AI phát hiện biên lai giả mạo.', 'whp'); ?></div>
                </div>
                <div class="wpaap-ns-tip">
                    <div class="wpaap-ns-tip-icon">&#x1F517;</div>
                    <div class="wpaap-ns-tip-text"><strong>Discord</strong> <?php esc_html_e('phù hợp cho team — mọi thành viên đều thấy thông báo cùng lúc.', 'whp'); ?></div>
                </div>
                <div class="wpaap-ns-tip">
                    <div class="wpaap-ns-tip-icon">&#x26A1;</div>
                    <div class="wpaap-ns-tip-text"><strong>Webhook</strong> <?php esc_html_e('cho phép tích hợp với CRM, ERP hoặc hệ thống nội bộ.', 'whp'); ?></div>
                </div>
            </div>
            <span class="wpaap-ns-tips-more"><?php esc_html_e('Xem tất cả mẹo', 'whp'); ?> &rarr;</span>
        </div>
    </div>

    <!-- 4. Thống kê nhanh -->
    <div class="wpaap-nsc">
        <div class="wpaap-nsc-hd">
            <div class="wpaap-nsc-hd-icon blue">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
            <span class="wpaap-nsc-title"><?php esc_html_e('Thống kê nhanh', 'whp'); ?></span>
            <span style="font-size:10.5px;color:#94a3b8;margin-left:auto;"><?php esc_html_e('Hôm nay', 'whp'); ?></span>
        </div>
        <div class="wpaap-nsc-body">
            <?php
            $active_ch = ($email_enable === '1' ? 1 : 0) + ($discord_enable === '1' ? 1 : 0) + ($webhook_enable === '1' ? 1 : 0);
            ?>
            <div class="wpaap-ns-qrow">
                <span class="wpaap-ns-qrow-lbl"><?php esc_html_e('Kênh đang hoạt động', 'whp'); ?></span>
                <span class="wpaap-ns-qrow-val <?php echo $active_ch > 0 ? 'ok' : ''; ?>"><?php echo $active_ch; ?>/3</span>
            </div>
            <div class="wpaap-ns-qrow">
                <span class="wpaap-ns-qrow-lbl"><?php esc_html_e('Thông báo đã gửi', 'whp'); ?></span>
                <span class="wpaap-ns-qrow-val">—</span>
            </div>
            <div class="wpaap-ns-qrow">
                <span class="wpaap-ns-qrow-lbl"><?php esc_html_e('Thất bại', 'whp'); ?></span>
                <span class="wpaap-ns-qrow-val">—</span>
            </div>
            <div style="margin-top:10px;">
                <svg viewBox="0 0 240 38" style="width:100%;height:38px;" fill="none">
                    <polyline points="0,32 30,26 60,28 90,14 120,20 150,10 180,16 210,8 240,12"
                        stroke="#e11d48" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    <polyline points="0,32 30,26 60,28 90,14 120,20 150,10 180,16 210,8 240,12 240,38 0,38"
                        fill="rgba(225,29,72,.07)"/>
                </svg>
            </div>
        </div>
    </div>

</aside><!-- /sidebar -->

</div><!-- /layout -->
    <?php
}
