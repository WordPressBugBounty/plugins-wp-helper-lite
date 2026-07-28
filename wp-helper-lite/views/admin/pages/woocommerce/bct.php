<?php defined('ABSPATH') || exit; ?>
<?php
$whp_bct_status        = whp_bct_get_status();
$whp_bct_link          = whp_get_setting('whp_bct_link');
$whp_bct_verified_name = whp_get_setting('whp_bct_verified_name');

$whp_bct_logo_mode = whp_bct_setting_or_default('whp_bct_logo_mode', 'auto');
$whp_bct_logo_url  = whp_get_setting('whp_bct_logo_url') ?: whp_bct_default_logo_url($whp_bct_status);

$whp_bct_pos_shortcode = whp_bct_setting_or_default('whp_bct_pos_shortcode', '1');

$whp_bct_align         = whp_bct_setting_or_default('whp_bct_align', 'center');
$whp_bct_width         = whp_bct_setting_or_default('whp_bct_width', '130');
$whp_bct_max_width     = whp_bct_setting_or_default('whp_bct_max_width', '200');
$whp_bct_margin_top    = whp_bct_setting_or_default('whp_bct_margin_top', '20');
$whp_bct_margin_bottom = whp_bct_setting_or_default('whp_bct_margin_bottom', '20');

$whp_bct_fx_none         = whp_get_setting('whp_bct_fx_none') == '1';
$whp_bct_fx_hover_scale  = whp_bct_setting_or_default('whp_bct_fx_hover_scale', '1');
$whp_bct_fx_hover_shadow = whp_bct_setting_or_default('whp_bct_fx_hover_shadow', '1');
$whp_bct_fx_rounded      = whp_bct_setting_or_default('whp_bct_fx_rounded', '1');

$whp_bct_custom_html_saved = whp_get_setting('whp_bct_custom_html');
$whp_bct_default_template  = whp_bct_default_html_template();
if (is_string($whp_bct_custom_html_saved) && trim($whp_bct_custom_html_saved) !== '') {
    $whp_bct_custom_html = $whp_bct_custom_html_saved;
} else {
    // Chưa tuỳ chỉnh gì — điền sẵn liên kết & logo hiện tại thay vì để %LINK%/%LOGO% thô,
    // để khách hàng nhìn vào là hiểu ngay và có thể tự sửa trực tiếp nếu muốn.
    $whp_bct_custom_html = str_replace(
        ['%LINK%', '%LOGO%'],
        [esc_url($whp_bct_link ?: 'https://online.gov.vn'), esc_url($whp_bct_logo_url)],
        $whp_bct_default_template
    );
}

$whp_bct_status_labels = [
    ''           => __('Chưa đăng ký Bộ Công Thương', 'whp'),
    'registered' => __('Đã đăng ký Bộ Công Thương', 'whp'),
];

$whp_bct_shortcode_tag = '[whp_bct]';
?>
<style>
#whp-toast-wrap{position:fixed;top:52px;left:50%;transform:translateX(-50%);z-index:99999999;display:flex;flex-direction:column;align-items:center;gap:8px;pointer-events:none;}
.whp-toast{display:flex;align-items:center;gap:10px;padding:12px 20px 12px 16px;border-radius:12px;font-size:13.5px;font-weight:600;color:#fff;box-shadow:0 8px 28px rgba(0,0,0,.18);pointer-events:all;min-width:260px;max-width:440px;animation:wt-in .28s cubic-bezier(.34,1.56,.64,1);transition:opacity .25s,transform .25s;}
.whp-toast.wt-out{opacity:0;transform:translateY(-14px) scale(.96);}
.whp-toast.wt-success{background:linear-gradient(135deg,#059669,#047857);}
.whp-toast.wt-error{background:linear-gradient(135deg,#dc2626,#b91c1c);}
.whp-toast-icon{width:22px;height:22px;border-radius:50%;background:rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;}
.whp-toast-msg{flex:1;line-height:1.4;}
.whp-toast-close{background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;font-size:16px;padding:0;line-height:1;flex-shrink:0;}
.whp-toast-close:hover{color:#fff;}
@keyframes wt-in{from{opacity:0;transform:translateY(-10px) scale(.95)}to{opacity:1;transform:none}}

.whp-ty-dash { font-family:inherit; max-width:1200px; margin:0 auto 60px; padding:0 6px; box-sizing:border-box; }
.whp-ty-header-card {
    position:relative;
    background:linear-gradient(100deg,#ffffff 0%,#f0f4ff 45%,#e8f0fd 100%);
    border-radius:20px;
    box-shadow:0 4px 24px rgba(56,88,233,.1), 0 0 0 1px #dbe4fd;
    margin-bottom:20px; overflow:hidden;
    min-height:150px; display:flex; align-items:stretch;
}
.whp-ty-header-left { position:relative; z-index:2; padding:26px 36px; display:flex; flex-direction:column; justify-content:center; gap:8px; max-width:600px; flex-shrink:0; }
.whp-ty-header-title-row { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
.whp-ty-header-desc { color:#64748b; font-size:13px; line-height:1.55; margin:0; padding-left:56px; max-width:440px; }
.whp-ty-header-icon-box {
    width:44px; height:44px; border-radius:12px;
    background:linear-gradient(135deg,#3858e9,#2542c2);
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; box-shadow:0 4px 12px rgba(56,88,233,.3);
}
.whp-ty-header-right { position:absolute; inset:0 0 0 42%; overflow:hidden; pointer-events:none; }
@media (max-width:600px) { .whp-ty-header-card { min-height:auto; } .whp-ty-header-left { padding:20px; } .whp-ty-header-right { display:none; } }

.whp-ty-main { display:grid; grid-template-columns:1fr 380px; gap:20px; align-items:start; }
.whp-ty-main-right { position:sticky; top:24px; display:flex; flex-direction:column; gap:16px; }
@media (max-width:1080px) { .whp-ty-main { grid-template-columns:1fr; } .whp-ty-main-right { position:static; } }

.whp-ty-card { background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.07), 0 0 0 1px #e8edf3; margin-bottom:16px; overflow:hidden; }
.whp-ty-card-inner { padding:20px 22px; }
.whp-ty-card-head { display:flex; align-items:flex-start; gap:12px; padding-bottom:14px; margin-bottom:14px; border-bottom:1px solid #f1f5f9; }
.whp-ty-card-icon { width:36px; height:36px; border-radius:9px; background:#eff2fe; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-ty-card-head h3 { margin:0 0 3px; font-size:15px; font-weight:700; color:#0f172a; }
.whp-ty-card-head p  { margin:0; font-size:12.5px; color:#64748b; }
.whp-bct-head-actionable { justify-content:space-between; align-items:center; flex-wrap:wrap; row-gap:12px; }
.whp-bct-head-main { display:flex; align-items:flex-start; gap:12px; flex:1; min-width:220px; }
.whp-bct-head-actionable .whp-bct-mode-options { margin-bottom:0; flex-shrink:0; }

.whp-ty-btn-save { display:inline-flex; align-items:center; gap:7px; padding:11px 26px; border-radius:10px; border:none; background:#3858e9; color:#fff; font-size:13.5px; font-weight:600; cursor:pointer; box-shadow:0 4px 14px rgba(56,88,233,.28); transition:all .2s; }
.whp-ty-btn-save:hover { background:#2563eb; transform:translateY(-1px); box-shadow:0 6px 18px rgba(56,88,233,.34); }
.whp-ty-btn-save:disabled { opacity:.6; cursor:not-allowed; transform:none; }
.whp-ty-bottom-save-bar { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.08), 0 0 0 1px #e8edf3; padding:16px 24px; display:flex; align-items:center; justify-content:space-between; margin-top:4px; gap:16px; }
.whp-ty-save-hint { display:inline-flex; align-items:center; gap:9px; }
.whp-ty-save-hint-ic { width:30px; height:30px; border-radius:50%; flex-shrink:0; background:#eff2fe; display:flex; align-items:center; justify-content:center; }
.whp-ty-save-hint-txt strong { display:block; font-size:13px; font-weight:600; color:#334155; margin-bottom:1px; }
.whp-ty-save-hint-txt span { font-size:12px; color:#94a3b8; }
@media (max-width:600px) { .whp-ty-bottom-save-bar { flex-direction:column; align-items:stretch; } .whp-ty-btn-save { width:100%; justify-content:center; } }

/* ===== TOGGLE ===== */
.whp-ty-switch { position:relative; display:inline-block; width:42px; height:23px; flex-shrink:0; }
.whp-ty-switch input { opacity:0; width:0; height:0; }
.whp-ty-slider { position:absolute; inset:0; background:#cbd5e1; border-radius:25px; cursor:pointer; transition:background .22s; }
.whp-ty-slider::after { content:''; position:absolute; width:15px; height:15px; background:#fff; border-radius:50%; left:4px; top:4px; transition:transform .22s; box-shadow:0 1px 4px rgba(0,0,0,.18); }
.whp-ty-switch input:checked + .whp-ty-slider { background:#3858e9; }
.whp-ty-switch input:checked + .whp-ty-slider::after { transform:translateX(19px); }
.whp-ty-switch input:disabled + .whp-ty-slider { opacity:.4; cursor:not-allowed; }

.whp-bct-field { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:11px 0; border-bottom:1px solid #f1f5f9; }
.whp-bct-field:last-child { border-bottom:none; padding-bottom:0; }
.whp-bct-field:first-child { padding-top:0; }
.whp-bct-field-label { font-weight:600; font-size:13.5px; color:#0f172a; }
.whp-bct-field-desc { font-size:12px; color:#64748b; margin-top:2px; }

.whp-bct-radio { flex:1; min-width:150px; display:flex; align-items:center; gap:9px; padding:13px 16px; border-radius:10px; border:1.5px solid #e2e8f0; cursor:pointer; transition:all .15s; }
.whp-bct-radio:hover { border-color:#c7d2fe; background:#fafbff; }
.whp-bct-radio input { accent-color:#3858e9; width:16px; height:16px; }
.whp-bct-radio span { font-size:13.5px; font-weight:600; color:#334155; }
.whp-bct-radio.is-checked { border-color:#3858e9; background:#eff2fe; }
.whp-bct-radio.is-checked span { color:#2542c2; }

.whp-bct-status-row { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
.whp-bct-badge-preview { flex:1; min-width:180px; display:flex; align-items:center; gap:10px; padding:12px 16px; border-radius:10px; background:#f8fafc; border:1px solid #e2e8f0; }
.whp-bct-badge-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
.whp-bct-badge-preview.is-empty .whp-bct-badge-dot { background:#94a3b8; }
.whp-bct-badge-preview.is-registered .whp-bct-badge-dot { background:#16a34a; }
.whp-bct-badge-preview span.txt { font-size:13px; font-weight:600; color:#334155; }
.whp-bct-status-check-wrap { display:flex; align-items:center; gap:10px; flex-shrink:0; }
.whp-bct-status-scan { display:flex; align-items:center; gap:7px; position:relative; }
.whp-bct-status-scan span { font-size:12px; font-weight:700; color:#334155; }

.whp-bct-link-row { display:flex; gap:10px; flex-wrap:wrap; align-items:center; }
.whp-bct-link-row input[type=text] { flex:1; min-width:260px; padding:11px 14px; border-radius:9px; border:1.5px solid #e2e8f0; font-size:13.5px; color:#0f172a; }
.whp-bct-link-row input[type=text]:focus { outline:none; border-color:#3858e9; }
.whp-bct-btn { padding:10px 18px; border-radius:9px; border:none; font-size:13px; font-weight:600; cursor:pointer; white-space:nowrap; transition:all .15s; }
.whp-bct-btn-primary { background:#3858e9; color:#fff; }
.whp-bct-btn-primary:hover { background:#2563eb; }
.whp-bct-btn-primary:disabled { opacity:.6; cursor:not-allowed; }
.whp-bct-btn-ghost { background:#f1f5f9; color:#334155; }
.whp-bct-btn-ghost:disabled { opacity:.5; cursor:not-allowed; }
.whp-bct-btn-sm { padding:6px 12px; font-size:12px; }

.whp-bct-check-result { margin-top:14px; padding:12px 16px; border-radius:10px; font-size:12.5px; line-height:1.6; display:none; }
.whp-bct-check-result.is-ok { display:block; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
.whp-bct-check-result.is-error { display:block; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; }

.whp-bct-skeleton { display:inline-block; width:16px; height:16px; border-radius:50%; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; animation:whp-bct-spin .7s linear infinite; }
.whp-bct-skeleton.is-dark { border-color:rgba(56,88,233,.25); border-top-color:#3858e9; }
@keyframes whp-bct-spin { to { transform:rotate(360deg); } }

/* SECTION 3 — LOGO */
.whp-bct-mode-options { display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
.whp-bct-logo-preview-box { display:flex; align-items:center; gap:16px; padding:16px; border-radius:10px; background:#f8fafc; border:1px dashed #cbd5e1; }
.whp-bct-logo-preview-box img { max-width:160px; max-height:60px; object-fit:contain; background:#fff; border-radius:6px; padding:6px; box-shadow:0 1px 3px rgba(0,0,0,.08); }
.whp-bct-logo-preview-txt { font-size:12.5px; color:#64748b; }
.whp-bct-upload-row { margin-top:14px; display:none; }
.whp-bct-upload-row.is-active { display:flex; gap:10px; align-items:center; }

/* SECTION 4 — VỊ TRÍ */
.whp-bct-shortcode-box { display:none; margin-top:14px; align-items:center; gap:10px; padding:10px 14px; border-radius:9px; background:#f8fafc; border:1px solid #e2e8f0; }
.whp-bct-shortcode-box.is-active { display:flex; }
.whp-bct-shortcode-box code { font-size:13px; font-weight:600; color:#3858e9; flex:1; }

/* SECTION 5 — GIAO DIỆN */
.whp-bct-align-row { display:flex; gap:10px; margin-bottom:16px; }
.whp-bct-align-btn { flex:1; padding:10px; border-radius:9px; border:1.5px solid #e2e8f0; background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; }
.whp-bct-align-btn.is-active { border-color:#3858e9; background:#eff2fe; }
.whp-bct-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.whp-bct-num-field label { display:block; font-size:12.5px; font-weight:600; color:#334155; margin-bottom:6px; }
.whp-bct-num-field input { width:100%; padding:9px 12px; border-radius:8px; border:1.5px solid #e2e8f0; font-size:13px; box-sizing:border-box; }
.whp-bct-num-field input:focus { outline:none; border-color:#3858e9; }
.whp-bct-num-field.is-readonly input { background:#f8fafc; color:#94a3b8; }

/* SECTION — HTML NÂNG CAO (chèn thủ công) */
.whp-bct-embed-label { font-size:12.5px; font-weight:600; color:#334155; margin-bottom:8px; }
.whp-bct-html-textarea { width:100%; min-height:110px; padding:12px 14px; border-radius:9px; border:1.5px solid #e2e8f0; font-family:Consolas,Monaco,monospace; font-size:12.5px; color:#0f172a; box-sizing:border-box; resize:vertical; }
.whp-bct-html-textarea:focus { outline:none; border-color:#3858e9; }
.whp-bct-html-textarea[readonly] { background:#f8fafc; color:#334155; }
.whp-bct-html-hint { font-size:11.5px; color:#94a3b8; margin-top:8px; }
.whp-bct-html-hint code { background:#f1f5f9; padding:1px 5px; border-radius:4px; }
.whp-bct-html-divider { height:1px; background:#f1f5f9; margin:20px 0; }

/* PREVIEW */
.whp-bct-device-row { display:flex; gap:6px; padding:4px; background:#f8fafc; border-radius:9px; border:1px solid #e2e8f0; margin-bottom:14px; }
.whp-bct-device-btn { flex:1; padding:7px; border-radius:7px; border:none; background:transparent; font-size:12px; font-weight:600; color:#64748b; cursor:pointer; }
.whp-bct-device-btn.is-active { background:#fff; color:#3858e9; box-shadow:0 1px 3px rgba(0,0,0,.1); }
.whp-bct-preview-frame { border:1px solid #e2e8f0; border-radius:10px; background:#f8fafc; padding:24px 12px; overflow:auto; transition:max-width .25s; margin:0 auto; }
.whp-bct-preview-frame.device-desktop { max-width:100%; }
.whp-bct-preview-frame.device-tablet { max-width:340px; }
.whp-bct-preview-frame.device-mobile { max-width:220px; }
#whp-bct-preview-style {}

/* SIDEBAR HƯỚNG DẪN */
.whp-bct-guide-list { list-style:none; margin:0; padding:0; display:flex; flex-direction:column; gap:10px; }
.whp-bct-guide-list li { display:flex; gap:10px; align-items:flex-start; font-size:12.5px; color:#334155; line-height:1.5; }
.whp-bct-guide-num { width:20px; height:20px; border-radius:50%; background:#eff2fe; color:#3858e9; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.whp-bct-guide-btn { display:flex; align-items:center; justify-content:center; gap:6px; width:100%; box-sizing:border-box; margin-top:14px; padding:10px; border-radius:9px; border:1px solid transparent; background:#3858e9; color:#fff; font-size:12.5px; font-weight:600; text-decoration:none; transition:background .15s; }
.whp-bct-guide-btn:hover { background:#2542c2; color:#fff; }
.whp-bct-guide-btn svg { flex-shrink:0; }
</style>
<div id="whp-toast-wrap"></div>

<div class="whp-ty-dash">

    <div id="whp-bct-save-notify-wrap"></div>

    <!-- HEADER -->
    <div class="whp-ty-header-card">
        <div class="whp-ty-header-left">
            <div class="whp-ty-header-title-row">
                <div class="whp-ty-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2 3 7v2h18V7L12 2z" fill="#fff" fill-opacity=".2" stroke="none"/>
                        <path d="M12 2 3 7v2h18V7L12 2z"/>
                        <path d="M5 10v8M9 10v8M15 10v8M19 10v8"/>
                        <path d="M3 20h18"/>
                    </svg>
                </div>
                <h1 style="font-size:22px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.3px;"><?php esc_html_e('Bộ Công Thương', 'whp'); ?></h1>
            </div>
            <p class="whp-ty-header-desc"><?php esc_html_e('Hỗ trợ cấu hình và hiển thị logo Bộ Công Thương đúng quy định cho website thương mại điện tử.', 'whp'); ?></p>
        </div>
        <div class="whp-ty-header-right">
            <svg viewBox="0 0 680 150" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="bct_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f0f4ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#e8f0fd" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#dbe4fd" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="bct_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(56,88,233,0.18)"/>
                    </filter>
                    <filter id="bct_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(56,88,233,0.12)"/>
                    </filter>
                </defs>
                <rect width="680" height="150" fill="url(#bct_hbg)"/>
                <circle cx="608" cy="14" r="62" fill="#3858e9" fill-opacity=".04"/>
                <circle cx="655" cy="132" r="40" fill="#5b7cfa" fill-opacity=".05"/>

                <!-- Website mockup with badge in footer -->
                <g filter="url(#bct_sh)">
                    <rect x="338" y="10" width="180" height="128" rx="12" fill="#fff"/>
                    <rect x="338" y="10" width="180" height="28" rx="12" fill="#3858e9" fill-opacity=".07"/>
                    <rect x="338" y="28" width="180" height="10" fill="#3858e9" fill-opacity=".07"/>
                    <circle cx="352" cy="24" r="3.5" fill="#ef4444" fill-opacity=".6"/>
                    <circle cx="363" cy="24" r="3.5" fill="#f59e0b" fill-opacity=".6"/>
                    <circle cx="374" cy="24" r="3.5" fill="#22c55e" fill-opacity=".6"/>
                    <rect x="388" y="18" width="116" height="11" rx="5.5" fill="#eff2fe"/>
                    <!-- content lines -->
                    <rect x="350" y="50" width="154" height="8" rx="4" fill="#eef2ff"/>
                    <rect x="350" y="64" width="118" height="6" rx="3" fill="#eef2ff"/>
                    <rect x="350" y="76" width="138" height="6" rx="3" fill="#eef2ff"/>
                    <!-- footer strip with badge -->
                    <rect x="338" y="104" width="180" height="34" fill="#f8fafc"/>
                    <g transform="translate(350,110)">
                        <circle cx="11" cy="11" r="11" fill="#3858e9"/>
                        <path d="M6 11l3.2 3.2L16 7.4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                    <rect x="376" y="108" width="82" height="6" rx="3" fill="#dbe4fd"/>
                    <rect x="376" y="118" width="58" height="5" rx="2.5" fill="#eef2ff"/>
                </g>

                <!-- Certificate seal -->
                <g filter="url(#bct_shSm)">
                    <circle cx="592" cy="88" r="28" fill="#3858e9"/>
                    <circle cx="592" cy="88" r="28" fill="url(#bct_hbg)" fill-opacity=".15"/>
                    <circle cx="592" cy="88" r="22" fill="none" stroke="#fff" stroke-opacity=".35" stroke-width="2" stroke-dasharray="3 4"/>
                    <path d="M580 89l7 7 15-15" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </g>
                <path d="M579 111l-8 19 13-6 6 13 8-19z" fill="#3858e9" fill-opacity=".25"/>
            </svg>
        </div>
    </div>

    <div class="whp-ty-main">
    <div class="whp-ty-main-left">

    <!-- SECTION 1: TRẠNG THÁI -->
    <div class="whp-ty-card">
        <div class="whp-ty-card-inner">
            <div class="whp-ty-card-head whp-bct-head-actionable">
                <div class="whp-bct-head-main">
                    <span class="whp-ty-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </span>
                    <div>
                        <h3><?php esc_html_e('Trạng thái xác thực', 'whp'); ?></h3>
                        <p><?php esc_html_e('Cho biết website hiện đang ở trạng thái nào với Bộ Công Thương', 'whp'); ?></p>
                    </div>
                </div>
                <div class="whp-bct-status-check-wrap">
                    <button type="button" class="whp-bct-btn whp-bct-btn-primary" id="whp-bct-status-check-btn"><?php esc_html_e('Kiểm tra', 'whp'); ?></button>
                    <div class="whp-bct-status-scan" id="whp-bct-status-scan" style="display:none">
                        <svg width="28" height="28" viewBox="0 0 28 28" style="transform:rotate(-90deg)">
                            <circle cx="14" cy="14" r="11" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                            <circle id="whp-bct-status-scan-ring" cx="14" cy="14" r="11" fill="none" stroke="#3858e9" stroke-width="3" stroke-linecap="round" stroke-dasharray="69.1" stroke-dashoffset="69.1"/>
                        </svg>
                        <span id="whp-bct-status-scan-pct">0%</span>
                    </div>
                </div>
            </div>
            <div class="whp-bct-status-row">
                <div class="whp-bct-badge-preview <?php echo $whp_bct_status === '' ? 'is-empty' : 'is-registered'; ?>" id="whp-bct-badge-preview">
                    <span class="whp-bct-badge-dot"></span>
                    <span class="txt" id="whp-bct-badge-preview-txt"><?php echo esc_html($whp_bct_status_labels[$whp_bct_status]); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: LIÊN KẾT XÁC THỰC -->
    <div class="whp-ty-card">
        <div class="whp-ty-card-inner">
            <div class="whp-ty-card-head whp-bct-head-actionable">
                <div class="whp-bct-head-main">
                    <span class="whp-ty-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </span>
                    <div>
                        <h3><?php esc_html_e('Liên kết Bộ Công Thương', 'whp'); ?></h3>
                        <p><?php esc_html_e('Dán liên kết xác thực từ online.gov.vn để hệ thống kiểm tra', 'whp'); ?></p>
                    </div>
                </div>
                <div class="whp-bct-status-check-wrap">
                    <button type="button" class="whp-bct-btn whp-bct-btn-primary" id="whp-bct-check-btn"><?php esc_html_e('Kiểm tra', 'whp'); ?></button>
                    <div class="whp-bct-status-scan" id="whp-bct-check-scan" style="display:none">
                        <svg width="28" height="28" viewBox="0 0 28 28" style="transform:rotate(-90deg)">
                            <circle cx="14" cy="14" r="11" fill="none" stroke="#e2e8f0" stroke-width="3"/>
                            <circle id="whp-bct-check-scan-ring" cx="14" cy="14" r="11" fill="none" stroke="#3858e9" stroke-width="3" stroke-linecap="round" stroke-dasharray="69.1" stroke-dashoffset="69.1"/>
                        </svg>
                        <span id="whp-bct-check-scan-pct">0%</span>
                    </div>
                </div>
            </div>
            <div class="whp-bct-link-row">
                <input type="text" id="whp-bct-link-input" value="<?php echo esc_attr($whp_bct_link); ?>" placeholder="<?php esc_attr_e('Dán liên kết xác thực từ online.gov.vn', 'whp'); ?>">
            </div>
            <div class="whp-bct-check-result" id="whp-bct-check-result"></div>
            <input type="hidden" id="whp-bct-verified-name" value="<?php echo esc_attr($whp_bct_verified_name); ?>">
        </div>
    </div>

    <!-- SECTION 3: LOGO -->
    <div class="whp-ty-card">
        <div class="whp-ty-card-inner">
            <div class="whp-ty-card-head whp-bct-head-actionable">
                <div class="whp-bct-head-main">
                    <span class="whp-ty-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    </span>
                    <div>
                        <h3><?php esc_html_e('Logo hiển thị', 'whp'); ?></h3>
                        <p><?php esc_html_e('Chọn cách lấy logo hiển thị lên website', 'whp'); ?></p>
                    </div>
                </div>
                <div class="whp-bct-mode-options" id="whp-bct-logo-mode-options">
                    <label class="whp-bct-radio<?php echo $whp_bct_logo_mode === 'auto' ? ' is-checked' : ''; ?>">
                        <input type="radio" name="whp_bct_logo_mode" value="auto" <?php checked($whp_bct_logo_mode, 'auto'); ?>>
                        <span><?php esc_html_e('Mặc định (ảnh chính thức)', 'whp'); ?></span>
                    </label>
                    <label class="whp-bct-radio<?php echo $whp_bct_logo_mode === 'upload' ? ' is-checked' : ''; ?>">
                        <input type="radio" name="whp_bct_logo_mode" value="upload" <?php checked($whp_bct_logo_mode, 'upload'); ?>>
                        <span><?php esc_html_e('Upload logo', 'whp'); ?></span>
                    </label>
                </div>
            </div>
            <div class="whp-bct-logo-preview-box">
                <img id="whp-bct-logo-preview-img" src="<?php echo esc_url($whp_bct_logo_url); ?>" alt="">
                <span class="whp-bct-logo-preview-txt" id="whp-bct-logo-mode-hint">
                    <?php esc_html_e('Dùng ảnh logo chính thức có sẵn của plugin. Nếu muốn dùng ảnh khác, chọn "Upload logo".', 'whp'); ?>
                </span>
            </div>
            <div class="whp-bct-upload-row<?php echo $whp_bct_logo_mode === 'upload' ? ' is-active' : ''; ?>" id="whp-bct-upload-row">
                <button type="button" class="whp-bct-btn whp-bct-btn-ghost" id="whp-bct-choose-image-btn"><?php esc_html_e('Chọn ảnh', 'whp'); ?></button>
                <span class="whp-bct-logo-preview-txt"><?php esc_html_e('Hỗ trợ PNG, JPG, WEBP', 'whp'); ?></span>
            </div>
            <input type="hidden" id="whp-bct-logo-url" value="<?php echo esc_attr($whp_bct_logo_url); ?>">
        </div>
    </div>

    <!-- SECTION 4: HIỂN THỊ -->
    <div class="whp-ty-card">
        <div class="whp-ty-card-inner">
            <div class="whp-ty-card-head">
                <span class="whp-ty-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/></svg>
                </span>
                <div>
                    <h3><?php esc_html_e('Vị trí hiển thị', 'whp'); ?></h3>
                    <p><?php esc_html_e('Chọn nơi hiển thị logo trên website', 'whp'); ?></p>
                </div>
            </div>
            <div class="whp-bct-field">
                <div><div class="whp-bct-field-label"><?php esc_html_e('Shortcode', 'whp'); ?></div><div class="whp-bct-field-desc"><?php esc_html_e('Chèn thủ công vào trang/bài viết bất kỳ', 'whp'); ?></div></div>
                <label class="whp-ty-switch"><input type="checkbox" id="whp-bct-pos-shortcode" value="1" <?php checked($whp_bct_pos_shortcode, '1'); ?>><span class="whp-ty-slider"></span></label>
            </div>
            <div class="whp-bct-shortcode-box<?php echo $whp_bct_pos_shortcode === '1' ? ' is-active' : ''; ?>" id="whp-bct-shortcode-box">
                <code><?php echo esc_html($whp_bct_shortcode_tag); ?></code>
                <button type="button" class="whp-bct-btn whp-bct-btn-ghost whp-bct-btn-sm" id="whp-bct-copy-shortcode"><?php esc_html_e('Copy', 'whp'); ?></button>
            </div>
        </div>
    </div>

    <!-- SECTION: HTML NÂNG CAO (chèn thủ công) -->
    <div class="whp-ty-card">
        <div class="whp-ty-card-inner">
            <div class="whp-ty-card-head">
                <span class="whp-ty-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                </span>
                <div>
                    <h3><?php esc_html_e('Mã HTML để chèn thủ công', 'whp'); ?></h3>
                    <p><?php esc_html_e('Copy mã bên dưới rồi tự dán vào footer/khối HTML tuỳ chỉnh của theme', 'whp'); ?></p>
                </div>
            </div>

            <div class="whp-bct-embed-label"><?php esc_html_e('Mã HTML sẵn sàng dùng — copy và dán trực tiếp', 'whp'); ?></div>
            <textarea class="whp-bct-html-textarea" id="whp-bct-embed-code-output" readonly></textarea>
            <button type="button" class="whp-bct-btn whp-bct-btn-primary whp-bct-btn-sm" id="whp-bct-copy-embed-code" style="margin-top:10px;"><?php esc_html_e('Copy mã HTML', 'whp'); ?></button>

            <div class="whp-bct-html-divider"></div>

            <div class="whp-bct-embed-label"><?php esc_html_e('Tuỳ chỉnh cấu trúc HTML (nâng cao)', 'whp'); ?></div>
            <textarea class="whp-bct-html-textarea" id="whp-bct-custom-html"><?php echo esc_textarea($whp_bct_custom_html); ?></textarea>
            <div class="whp-bct-html-hint"><?php echo wp_kses(sprintf(__('Mã đã tự động điền sẵn liên kết và logo hiện tại của bạn — có thể chỉnh sửa trực tiếp (đổi liên kết, đổi ảnh, thêm class...) nếu có nhu cầu khác. Muốn mã tự cập nhật mỗi khi đổi liên kết/logo ở trên, dùng %1$s và %2$s thay cho giá trị thật.', 'whp'), '<code>%LINK%</code>', '<code>%LOGO%</code>'), ['code' => []]); ?></div>
        </div>
    </div>

    <!-- SECTION 5: GIAO DIỆN -->
    <div class="whp-ty-card">
        <div class="whp-ty-card-inner">
            <div class="whp-ty-card-head">
                <span class="whp-ty-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="11" cy="11" r="2"/></svg>
                </span>
                <div>
                    <h3><?php esc_html_e('Giao diện hiển thị', 'whp'); ?></h3>
                    <p><?php esc_html_e('Căn chỉnh kích thước và vị trí logo', 'whp'); ?></p>
                </div>
            </div>
            <div class="whp-bct-align-row" id="whp-bct-align-row">
                <?php foreach (['left' => __('Left', 'whp'), 'center' => __('Center', 'whp'), 'right' => __('Right', 'whp')] as $aval => $albl) : ?>
                <button type="button" class="whp-bct-align-btn<?php echo $whp_bct_align === $aval ? ' is-active' : ''; ?>" data-align="<?php echo esc_attr($aval); ?>">
                    <?php if ($aval === 'left') : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="15" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="15" y2="18"/></svg>
                    <?php elseif ($aval === 'right') : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="9" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="9" y1="18" x2="21" y2="18"/></svg>
                    <?php else : ?>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="18" y2="18"/></svg>
                    <?php endif; ?>
                </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="whp-bct-align" value="<?php echo esc_attr($whp_bct_align); ?>">
            <div class="whp-bct-grid-2">
                <div class="whp-bct-num-field"><label><?php esc_html_e('Width (px)', 'whp'); ?></label><input type="number" min="0" id="whp-bct-width" value="<?php echo esc_attr($whp_bct_width); ?>"></div>
                <div class="whp-bct-num-field"><label><?php esc_html_e('Max Width (px)', 'whp'); ?></label><input type="number" min="0" id="whp-bct-max-width" value="<?php echo esc_attr($whp_bct_max_width); ?>"></div>
                <div class="whp-bct-num-field is-readonly"><label><?php esc_html_e('Height', 'whp'); ?></label><input type="text" value="Auto" readonly></div>
                <div class="whp-bct-num-field"><label><?php esc_html_e('Margin Top (px)', 'whp'); ?></label><input type="number" min="0" id="whp-bct-margin-top" value="<?php echo esc_attr($whp_bct_margin_top); ?>"></div>
                <div class="whp-bct-num-field"><label><?php esc_html_e('Margin Bottom (px)', 'whp'); ?></label><input type="number" min="0" id="whp-bct-margin-bottom" value="<?php echo esc_attr($whp_bct_margin_bottom); ?>"></div>
            </div>
        </div>
    </div>

    <!-- SECTION 6: HIỆU ỨNG -->
    <div class="whp-ty-card">
        <div class="whp-ty-card-inner">
            <div class="whp-ty-card-head">
                <span class="whp-ty-card-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l1.6 4.4L18 8l-4.4 1.6L12 14l-1.6-4.4L6 8l4.4-1.6L12 2z"/></svg>
                </span>
                <div>
                    <h3><?php esc_html_e('Hiệu ứng', 'whp'); ?></h3>
                    <p><?php esc_html_e('Tùy chọn hiệu ứng tương tác cho logo', 'whp'); ?></p>
                </div>
            </div>
            <div class="whp-bct-field">
                <div class="whp-bct-field-label"><?php esc_html_e('Hover Scale', 'whp'); ?></div>
                <label class="whp-ty-switch"><input type="checkbox" id="whp-bct-fx-hover-scale" class="whp-bct-fx-toggle" value="1" <?php checked($whp_bct_fx_hover_scale, '1'); ?>><span class="whp-ty-slider"></span></label>
            </div>
            <div class="whp-bct-field">
                <div class="whp-bct-field-label"><?php esc_html_e('Hover Shadow', 'whp'); ?></div>
                <label class="whp-ty-switch"><input type="checkbox" id="whp-bct-fx-hover-shadow" class="whp-bct-fx-toggle" value="1" <?php checked($whp_bct_fx_hover_shadow, '1'); ?>><span class="whp-ty-slider"></span></label>
            </div>
            <div class="whp-bct-field">
                <div class="whp-bct-field-label"><?php esc_html_e('Bo góc', 'whp'); ?></div>
                <label class="whp-ty-switch"><input type="checkbox" id="whp-bct-fx-rounded" class="whp-bct-fx-toggle" value="1" <?php checked($whp_bct_fx_rounded, '1'); ?>><span class="whp-ty-slider"></span></label>
            </div>
            <div class="whp-bct-field">
                <div class="whp-bct-field-label"><?php esc_html_e('Không hiệu ứng', 'whp'); ?></div>
                <label class="whp-ty-switch"><input type="checkbox" id="whp-bct-fx-none" value="1" <?php checked($whp_bct_fx_none, true); ?>><span class="whp-ty-slider"></span></label>
            </div>
        </div>
    </div>

    </div><!-- .whp-ty-main-left -->

    <div class="whp-ty-main-right">

        <!-- PREVIEW -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-ty-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                    <div><h3><?php esc_html_e('Xem trước', 'whp'); ?></h3><p><?php esc_html_e('Cập nhật theo thời gian thực', 'whp'); ?></p></div>
                </div>
                <div class="whp-bct-device-row">
                    <button type="button" class="whp-bct-device-btn is-active" data-device="desktop"><?php esc_html_e('Desktop', 'whp'); ?></button>
                    <button type="button" class="whp-bct-device-btn" data-device="tablet"><?php esc_html_e('Tablet', 'whp'); ?></button>
                    <button type="button" class="whp-bct-device-btn" data-device="mobile"><?php esc_html_e('Mobile', 'whp'); ?></button>
                </div>
                <div class="whp-bct-preview-frame device-desktop" id="whp-bct-preview-frame">
                    <div id="whp-bct-preview-content"></div>
                </div>
            </div>
        </div>

        <!-- SIDEBAR HƯỚNG DẪN -->
        <div class="whp-ty-card">
            <div class="whp-ty-card-inner">
                <div class="whp-ty-card-head">
                    <span class="whp-ty-card-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </span>
                    <div><h3><?php esc_html_e('Hướng dẫn sử dụng', 'whp'); ?></h3></div>
                </div>
                <ul class="whp-bct-guide-list">
                    <?php
                    $whp_bct_guide_steps = [
                        __('Đăng ký website tại online.gov.vn', 'whp'),
                        __('Chờ duyệt', 'whp'),
                        __('Sao chép liên kết xác thực', 'whp'),
                        __('Dán vào plugin và bấm "Kiểm tra"', 'whp'),
                        __('Chọn logo (mặc định hoặc tự upload)', 'whp'),
                        __('Chọn vị trí hiển thị', 'whp'),
                        __('Lưu cấu hình', 'whp'),
                    ];
                    foreach ($whp_bct_guide_steps as $i => $step) :
                    ?>
                    <li><span class="whp-bct-guide-num"><?php echo esc_html($i + 1); ?></span><span><?php echo esc_html($step); ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <a href="https://www.matbao.net/ten-mien/thong-bao-website-voi-bo-cong-thuong" target="_blank" rel="noopener noreferrer" class="whp-bct-guide-btn"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg><?php esc_html_e('Đăng ký bộ công thương ngay', 'whp'); ?></a>
            </div>
        </div>

    </div><!-- .whp-ty-main-right -->
    </div><!-- .whp-ty-main -->

    <!-- BOTTOM SAVE BAR (standalone, full width) -->
    <div class="whp-ty-bottom-save-bar">
        <div class="whp-ty-save-hint">
            <span class="whp-ty-save-hint-ic">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </span>
            <span class="whp-ty-save-hint-txt"><strong><?php esc_html_e('Lưu cấu hình', 'whp'); ?></strong><span><?php esc_html_e('Cập nhật ngay, không cần tải lại trang', 'whp'); ?></span></span>
        </div>
        <button type="button" class="whp-ty-btn-save" id="whp-bct-save-btn"><?php esc_html_e('Lưu cấu hình', 'whp'); ?></button>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var ajaxUrl = <?php echo wp_json_encode( admin_url('admin-ajax.php') ); ?>;
    var nonce   = <?php echo wp_json_encode( wp_create_nonce('whp_bct_admin') ); ?>;
    var labels  = <?php echo wp_json_encode( $whp_bct_status_labels ); ?>;
    var defaultTemplate = <?php echo wp_json_encode( $whp_bct_default_template ); ?>;
    var defaultLogoUrl  = <?php echo wp_json_encode( whp_bct_default_logo_url() ); ?>;

    function toast(message, isSuccess) {
        var wrap = document.getElementById('whp-toast-wrap');
        if (!wrap) return;
        var el = document.createElement('div');
        el.className = 'whp-toast ' + (isSuccess ? 'wt-success' : 'wt-error');
        el.innerHTML = '<span class="whp-toast-icon">' + (isSuccess ? '✓' : '!') + '</span><span class="whp-toast-msg"></span><button type="button" class="whp-toast-close">×</button>';
        el.querySelector('.whp-toast-msg').textContent = message;
        wrap.appendChild(el);
        function remove() { el.classList.add('wt-out'); setTimeout(function () { el.remove(); }, 260); }
        el.querySelector('.whp-toast-close').addEventListener('click', remove);
        setTimeout(remove, 4000);
    }

    function showSaveNotify() {
        var wrap = document.getElementById('whp-bct-save-notify-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<div class="mb-wph-notify">' + <?php echo wp_json_encode( __('Cập nhật cài đặt thành công', 'whp') ); ?> + '</div>';
        window.scrollTo({ top: 0, behavior: 'smooth' });
        setTimeout(function () {
            var el = wrap.firstElementChild;
            if (!el) return;
            el.style.transition = 'opacity .3s, transform .3s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(function () { wrap.innerHTML = ''; }, 300);
        }, 3000);
    }

    function post(action, data) {
        var body = new URLSearchParams();
        body.append('action', action);
        body.append('nonce', nonce);
        for (var k in data) { body.append(k, data[k]); }
        return fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body }).then(function (r) { return r.json(); });
    }

    // ===== Section 1: trạng thái (nút Kiểm tra, không còn chọn tay) =====
    var currentStatus = <?php echo wp_json_encode( $whp_bct_status ); ?>;
    var preview   = document.getElementById('whp-bct-badge-preview');
    var previewTx = document.getElementById('whp-bct-badge-preview-txt');

    function getCurrentStatus() {
        return currentStatus;
    }

    function applyStatusResult(registered) {
        currentStatus = registered ? 'registered' : '';
        preview.className = 'whp-bct-badge-preview ' + (registered ? 'is-registered' : 'is-empty');
        previewTx.textContent = registered ? labels.registered : labels[''];
    }

    var STATUS_CIRC = 69.1;

    function createScanUI(btn, scan, ring, pctEl) {
        var timer = null;
        return {
            start: function () {
                btn.disabled = true;
                btn.style.display = 'none';
                scan.style.display = 'flex';
                var pct = 0;
                timer = setInterval(function () {
                    pct += Math.random() * 12 + 6;
                    if (pct > 92) pct = 92; // giữ dưới 100% cho tới khi có kết quả thật
                    ring.style.strokeDashoffset = String(STATUS_CIRC * (1 - pct / 100));
                    pctEl.textContent = Math.floor(pct) + '%';
                }, 180);
            },
            finish: function () {
                clearInterval(timer);
                ring.style.strokeDashoffset = '0';
                pctEl.textContent = '100%';
            },
            reset: function () {
                scan.style.display = 'none';
                ring.style.strokeDashoffset = String(STATUS_CIRC);
                pctEl.textContent = '0%';
                btn.style.display = '';
                btn.disabled = false;
            }
        };
    }

    var statusCheckBtn  = document.getElementById('whp-bct-status-check-btn');
    var statusScanUI = createScanUI(
        statusCheckBtn,
        document.getElementById('whp-bct-status-scan'),
        document.getElementById('whp-bct-status-scan-ring'),
        document.getElementById('whp-bct-status-scan-pct')
    );

    statusCheckBtn.addEventListener('click', function () {
        statusScanUI.start();

        var linkVal = document.getElementById('whp-bct-link-input').value.trim();
        post('whp_bct_check_status', { link: linkVal }).then(function (res) {
            statusScanUI.finish();
            setTimeout(function () {
                statusScanUI.reset();
                if (res && res.success) {
                    var registered = !!(res.data && res.data.registered);
                    applyStatusResult(registered);
                    var method = (res.data && res.data.method) || 'none';
                    var methodMsgs = {
                        link:            <?php echo wp_json_encode( __('Đã xác thực qua liên kết Bộ Công Thương!', 'whp') ); ?>,
                        homepage:        <?php echo wp_json_encode( __('Đã phát hiện badge trên trang chủ website!', 'whp') ); ?>,
                        domain_mismatch: <?php echo wp_json_encode( __('Liên kết hợp lệ nhưng không khớp tên miền website này — vui lòng dùng đúng liên kết đăng ký của website hiện tại.', 'whp') ); ?>,
                        none:            <?php echo wp_json_encode( __('Chưa xác thực được — kiểm tra lại liên kết, hoặc badge đã gắn lên trang chủ chưa.', 'whp') ); ?>
                    };
                    toast(methodMsgs[method] || methodMsgs.none, registered);
                } else {
                    toast(<?php echo wp_json_encode( __('Không thể kiểm tra, vui lòng thử lại.', 'whp') ); ?>, false);
                }
            }, 300);
        }).catch(function () {
            statusScanUI.finish();
            setTimeout(function () {
                statusScanUI.reset();
                toast(<?php echo wp_json_encode( __('Không thể kiểm tra, vui lòng thử lại.', 'whp') ); ?>, false);
            }, 300);
        });
    });

    // ===== Section 2: kiểm tra liên kết =====
    var checkBtn    = document.getElementById('whp-bct-check-btn');
    var linkInput   = document.getElementById('whp-bct-link-input');
    var resultBox   = document.getElementById('whp-bct-check-result');
    var verifiedName = document.getElementById('whp-bct-verified-name');
    var checkScanUI = createScanUI(
        checkBtn,
        document.getElementById('whp-bct-check-scan'),
        document.getElementById('whp-bct-check-scan-ring'),
        document.getElementById('whp-bct-check-scan-pct')
    );

    checkBtn.addEventListener('click', function () {
        var link = linkInput.value.trim();
        checkScanUI.start();
        resultBox.className = 'whp-bct-check-result';
        resultBox.style.display = 'none';

        post('whp_bct_check_link', { link: link }).then(function (res) {
            checkScanUI.finish();
            setTimeout(function () {
                checkScanUI.reset();
                if (res && res.success) {
                    verifiedName.value = (res.data && res.data.name) ? res.data.name : '';
                    var msg = '✔ ' + <?php echo wp_json_encode( __('Liên kết hợp lệ', 'whp') ); ?>;
                    if (res.data && res.data.name) {
                        msg += '<br><strong>' + <?php echo wp_json_encode( __('Tên website:', 'whp') ); ?> + '</strong> ' + res.data.name;
                    }
                    resultBox.className = 'whp-bct-check-result is-ok';
                    resultBox.innerHTML = msg;
                    toast(<?php echo wp_json_encode( __('Liên kết hợp lệ!', 'whp') ); ?>, true);
                } else {
                    var errMsg = (res && res.data && res.data.message) ? res.data.message : <?php echo wp_json_encode( __('Không thể kiểm tra liên kết.', 'whp') ); ?>;
                    resultBox.className = 'whp-bct-check-result is-error';
                    resultBox.textContent = errMsg;
                    toast(errMsg, false);
                }
            }, 300);
        }).catch(function () {
            checkScanUI.finish();
            setTimeout(function () {
                checkScanUI.reset();
                resultBox.className = 'whp-bct-check-result is-error';
                resultBox.textContent = <?php echo wp_json_encode( __('Không thể truy cập đường dẫn.', 'whp') ); ?>;
                toast(<?php echo wp_json_encode( __('Không thể truy cập đường dẫn.', 'whp') ); ?>, false);
            }, 300);
        });
    });

    // ===== Section 3: logo =====
    var logoModeOptions = document.querySelectorAll('#whp-bct-logo-mode-options input[name="whp_bct_logo_mode"]');
    var logoPreviewImg  = document.getElementById('whp-bct-logo-preview-img');
    var logoModeHint    = document.getElementById('whp-bct-logo-mode-hint');
    var uploadRow       = document.getElementById('whp-bct-upload-row');
    var logoUrlInput    = document.getElementById('whp-bct-logo-url');
    var chooseImageBtn  = document.getElementById('whp-bct-choose-image-btn');

    var logoHintAuto        = <?php echo wp_json_encode( __('Dùng ảnh logo chính thức có sẵn của plugin. Nếu muốn dùng ảnh khác, chọn "Upload logo".', 'whp') ); ?>;
    var logoHintUploadEmpty = <?php echo wp_json_encode( __('Chưa có logo tuỳ chỉnh — bấm "Chọn ảnh" bên dưới để tải logo của bạn lên.', 'whp') ); ?>;
    var logoHintUploadSet   = <?php echo wp_json_encode( __('Đây là logo tuỳ chỉnh bạn đã tải lên. Bấm "Chọn ảnh" để đổi ảnh khác.', 'whp') ); ?>;

    function updateLogoModeUi() {
        var mode = 'auto';
        logoModeOptions.forEach(function (r) {
            r.closest('.whp-bct-radio').classList.toggle('is-checked', r.checked);
            if (r.checked) mode = r.value;
        });
        uploadRow.classList.toggle('is-active', mode === 'upload');
        if (mode === 'auto') {
            logoUrlInput.value = defaultLogoUrl;
            logoPreviewImg.src = defaultLogoUrl;
            logoModeHint.textContent = logoHintAuto;
        } else {
            var hasCustom = logoUrlInput.value && logoUrlInput.value !== defaultLogoUrl;
            logoModeHint.textContent = hasCustom ? logoHintUploadSet : logoHintUploadEmpty;
        }
    }
    updateLogoModeUi();
    logoModeOptions.forEach(function (r) { r.addEventListener('change', function () { updateLogoModeUi(); renderPreview(); }); });

    chooseImageBtn.addEventListener('click', function () {
        if (typeof wp === 'undefined' || !wp.media) { return; }
        var frame = wp.media({
            title: <?php echo wp_json_encode( __('Chọn logo Bộ Công Thương', 'whp') ); ?>,
            library: { type: 'image' },
            multiple: false,
            button: { text: <?php echo wp_json_encode( __('Sử dụng ảnh này', 'whp') ); ?> }
        });
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            logoUrlInput.value = attachment.url;
            logoPreviewImg.src = attachment.url;
            updateLogoModeUi();
            renderPreview();
        });
        frame.open();
    });

    // ===== Section 4: vị trí =====
    var posShortcode   = document.getElementById('whp-bct-pos-shortcode');
    var shortcodeBox   = document.getElementById('whp-bct-shortcode-box');
    posShortcode.addEventListener('change', function () { shortcodeBox.classList.toggle('is-active', posShortcode.checked); });
    document.getElementById('whp-bct-copy-shortcode').addEventListener('click', function () {
        var text = <?php echo wp_json_encode( $whp_bct_shortcode_tag ); ?>;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () { toast(<?php echo wp_json_encode( __('Đã sao chép shortcode!', 'whp') ); ?>, true); });
        }
    });

    // ===== Section 5: giao diện =====
    var alignInput = document.getElementById('whp-bct-align');
    var alignBtns  = document.querySelectorAll('#whp-bct-align-row .whp-bct-align-btn');
    alignBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            alignBtns.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            alignInput.value = btn.getAttribute('data-align');
            renderPreview();
        });
    });
    var widthInput  = document.getElementById('whp-bct-width');
    var maxWInput   = document.getElementById('whp-bct-max-width');
    var mtInput     = document.getElementById('whp-bct-margin-top');
    var mbInput     = document.getElementById('whp-bct-margin-bottom');
    [widthInput, maxWInput, mtInput, mbInput].forEach(function (el) { el.addEventListener('input', renderPreview); });

    // ===== Section 6: hiệu ứng =====
    var fxNone = document.getElementById('whp-bct-fx-none');
    var fxToggles = document.querySelectorAll('.whp-bct-fx-toggle');
    function updateFxDisabled() {
        fxToggles.forEach(function (t) { t.disabled = fxNone.checked; });
    }
    fxNone.addEventListener('change', function () { updateFxDisabled(); renderPreview(); });
    fxToggles.forEach(function (t) { t.addEventListener('change', renderPreview); });
    updateFxDisabled();

    // ===== HTML nâng cao: mã sẵn sàng dùng + copy =====
    var customHtmlInput = document.getElementById('whp-bct-custom-html');
    var embedCodeOutput  = document.getElementById('whp-bct-embed-code-output');
    customHtmlInput.addEventListener('input', renderPreview);

    document.getElementById('whp-bct-copy-embed-code').addEventListener('click', function () {
        var text = embedCodeOutput.value;
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(function () { toast(<?php echo wp_json_encode( __('Đã sao chép mã HTML!', 'whp') ); ?>, true); });
        }
    });

    function buildEmbedCode() {
        var link = linkInput.value.trim() || 'https://online.gov.vn';
        var logo = logoUrlInput.value || defaultLogoUrl;
        var tpl  = customHtmlInput.value.trim() !== '' ? customHtmlInput.value : defaultTemplate;
        var inner = tpl.split('%LINK%').join(link).split('%LOGO%').join(logo);

        var align  = alignInput.value || 'center';
        var justify = align === 'left' ? 'flex-start' : (align === 'right' ? 'flex-end' : 'center');
        var width  = parseInt(widthInput.value, 10) || 130;
        var maxW   = parseInt(maxWInput.value, 10) || 200;
        var mt     = parseInt(mtInput.value, 10) || 0;
        var mb     = parseInt(mbInput.value, 10) || 0;

        var fxNoneOn   = fxNone.checked;
        var fxScaleOn  = !fxNoneOn && document.getElementById('whp-bct-fx-hover-scale').checked;
        var fxShadowOn = !fxNoneOn && document.getElementById('whp-bct-fx-hover-shadow').checked;
        var fxRoundOn  = !fxNoneOn && document.getElementById('whp-bct-fx-rounded').checked;

        var cls = 'whp-bct-badge';
        var css = '.' + cls + '{display:flex;justify-content:' + justify + ';margin:' + mt + 'px 0 ' + mb + 'px;}';
        css += '.' + cls + ' img{width:' + width + 'px;max-width:' + maxW + 'px;height:auto;transition:transform .2s,box-shadow .2s;}';
        if (fxRoundOn) css += '.' + cls + ' img{border-radius:8px;}';
        if (fxScaleOn) css += '.' + cls + ':hover img{transform:scale(1.06);}';
        if (fxShadowOn) css += '.' + cls + ':hover img{box-shadow:0 6px 18px rgba(0,0,0,.18);}';

        return '<style>' + css + '</style>\n<div class="' + cls + '">' + inner + '</div>';
    }

    // ===== Preview realtime =====
    var previewFrame   = document.getElementById('whp-bct-preview-frame');
    var previewContent = document.getElementById('whp-bct-preview-content');
    document.querySelectorAll('.whp-bct-device-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.whp-bct-device-btn').forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            previewFrame.className = 'whp-bct-preview-frame device-' + btn.getAttribute('data-device');
        });
    });

    function renderPreview() {
        var link = linkInput.value.trim() || 'https://online.gov.vn';
        var logo = logoUrlInput.value || defaultLogoUrl;
        var tpl  = customHtmlInput.value.trim() !== '' ? customHtmlInput.value : defaultTemplate;
        var html = tpl.split('%LINK%').join(link).split('%LOGO%').join(logo);

        var align  = alignInput.value || 'center';
        var justify = align === 'left' ? 'flex-start' : (align === 'right' ? 'flex-end' : 'center');
        var width  = parseInt(widthInput.value, 10) || 180;
        var maxW   = parseInt(maxWInput.value, 10) || 300;
        var mt     = parseInt(mtInput.value, 10) || 0;
        var mb     = parseInt(mbInput.value, 10) || 0;

        var fxNoneOn   = fxNone.checked;
        var fxScaleOn  = !fxNoneOn && document.getElementById('whp-bct-fx-hover-scale').checked;
        var fxShadowOn = !fxNoneOn && document.getElementById('whp-bct-fx-hover-shadow').checked;
        var fxRoundOn  = !fxNoneOn && document.getElementById('whp-bct-fx-rounded').checked;

        var existingStyle = document.getElementById('whp-bct-preview-style');
        if (existingStyle) existingStyle.remove();
        var styleEl = document.createElement('style');
        styleEl.id = 'whp-bct-preview-style';
        var css = '#whp-bct-preview-content{display:flex;justify-content:' + justify + ';margin:' + mt + 'px 0 ' + mb + 'px;}';
        css += '#whp-bct-preview-content img{width:' + width + 'px;max-width:' + maxW + 'px;height:auto;transition:transform .2s,box-shadow .2s;}';
        if (fxRoundOn) css += '#whp-bct-preview-content img{border-radius:8px;}';
        if (fxScaleOn) css += '#whp-bct-preview-content:hover img{transform:scale(1.06);}';
        if (fxShadowOn) css += '#whp-bct-preview-content:hover img{box-shadow:0 6px 18px rgba(0,0,0,.18);}';
        styleEl.textContent = css;
        document.head.appendChild(styleEl);

        previewContent.innerHTML = html;
        embedCodeOutput.value = buildEmbedCode();
    }
    renderPreview();
    linkInput.addEventListener('input', renderPreview);

    // ===== Lưu cấu hình =====
    var saveBtn = document.getElementById('whp-bct-save-btn');
    saveBtn.addEventListener('click', function () {
        var logoMode = 'auto';
        logoModeOptions.forEach(function (r) { if (r.checked) logoMode = r.value; });

        saveBtn.disabled = true;
        var originalHtml = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="whp-bct-skeleton"></span> ' + <?php echo wp_json_encode( __('Đang lưu...', 'whp') ); ?>;

        post('whp_bct_save', {
            link: linkInput.value.trim(),
            verified_name: verifiedName.value,
            logo_mode: logoMode,
            logo_url: logoUrlInput.value,
            pos_shortcode: posShortcode.checked ? '1' : '0',
            align: alignInput.value,
            width: widthInput.value,
            max_width: maxWInput.value,
            margin_top: mtInput.value,
            margin_bottom: mbInput.value,
            fx_hover_scale: document.getElementById('whp-bct-fx-hover-scale').checked ? '1' : '0',
            fx_hover_shadow: document.getElementById('whp-bct-fx-hover-shadow').checked ? '1' : '0',
            fx_rounded: document.getElementById('whp-bct-fx-rounded').checked ? '1' : '0',
            fx_none: fxNone.checked ? '1' : '0',
            custom_html: customHtmlInput.value
        }).then(function (res) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            if (res && res.success) {
                showSaveNotify();
            } else {
                var saveErrMsg = (res && res.data && res.data.message) ? res.data.message : <?php echo wp_json_encode( __('Lưu thất bại, vui lòng thử lại.', 'whp') ); ?>;
                toast(saveErrMsg, false);
            }
        }).catch(function () {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
            toast(<?php echo wp_json_encode( __('Lưu thất bại, vui lòng thử lại.', 'whp') ); ?>, false);
        });
    });
});
</script>
