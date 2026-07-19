<?php
if (!defined('ABSPATH')) exit;

// ─── Load all field values from wp_options ────────────────────────────────
$option = get_option('whp_setting', []);

$all_fields = [
    'whp_security_remove_xmlrpc', 'whp_security_disable_copy',
    'whp_security_delete_wphead', 'whp_security_hide_wp_version',
    'whp_security_hide_theme_plugin', 'whp_security_change_login_url', 'whp_new_login_url',
    'whp_extention_editor_type', 'whp_extention_duplicate_page_post',
    'whp_extention_duplicate_menu', 'whp_extention_enable_404_redirect',
    'whp_extention_disable_emojis', 'whp_extention_remove_query_string',
    'whp_extention_disbale_wp_embeds', 'whp_extention_disbale_google_fonts',
    'whp_extention_disable_heartbeat_frontend', 'whp_extention_heartbeat_limit_admin',
    'whp_extention_notification', 'whp_extention_disbale_dashicons',
    'whp_extention_custom_login_theme', 'whp_extention_custom_login_logo',
    'whp_extention_custom_link', 'whp_extention_custom_link_new_tab', 'whp_extention_svg',
];

$toggle_fields = [
    'whp_security_remove_xmlrpc', 'whp_security_disable_copy', 'whp_security_delete_wphead',
    'whp_security_hide_wp_version', 'whp_security_hide_theme_plugin', 'whp_security_change_login_url',
    'whp_extention_duplicate_page_post', 'whp_extention_duplicate_menu', 'whp_extention_enable_404_redirect',
    'whp_extention_disable_emojis', 'whp_extention_remove_query_string', 'whp_extention_disbale_wp_embeds',
    'whp_extention_disbale_google_fonts', 'whp_extention_notification', 'whp_extention_disbale_dashicons',
    'whp_extention_disable_heartbeat_frontend', 'whp_extention_heartbeat_limit_admin',
    'whp_extention_custom_login_theme', 'whp_extention_svg',
    'whp_extention_custom_link_new_tab',
];

// Initialise variables from option
foreach ($all_fields as $f) {
    $$f = isset($option[$f]) ? $option[$f] : '';
}
$whp_extention_custom_login_logo = whp_get_valid_login_logo($whp_extention_custom_login_logo);

// Handle form submit
$isSubmit = 0;
if (isset($_POST['submit'])) {
    if (!wp_verify_nonce($_POST['_token'] ?? '', '_token')) exit();
    unset($_POST['submit']);
    $isSubmit = 1;
    $params = sanitize_data($_POST);
    foreach ($toggle_fields as $f) {
        $params[$f] = isset($params[$f]) ? '1' : '0';
    }
    foreach ($all_fields as $f) {
        $$f = $params[$f] ?? '';
    }
    // Không save placeholder URL — chỉ giữ giá trị DB cũ nếu POST không có logo thực
    $placeholder_url = MB_WHP_URL . 'assets/admin/images/icon.svg';
    if (!$whp_extention_custom_login_logo || $whp_extention_custom_login_logo === $placeholder_url) {
        $whp_extention_custom_login_logo = $option['whp_extention_custom_login_logo'] ?? '';
        $params['whp_extention_custom_login_logo'] = $whp_extention_custom_login_logo;
    }
    $whp_extention_custom_login_logo = whp_get_valid_login_logo($whp_extention_custom_login_logo);
    $allFields = whp_get_all_field();
    $params = $option ? array_merge($option, $params) : array_merge($allFields, $params);
    update_option('whp_setting', $params);
    whp_purge_page_cache();
    $option = $params; // refresh để template render đúng trạng thái mới
}

$listEditor = whp_get_list_editor();

// ─── Helper: is toggle on? ────────────────────────────────────────────────
function mb_seu_on($val) { return $val === '1' || $val === 1 || $val === true; }

whp_get_shared('header');
?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo esc_html__('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
/* ══════════════════════════════════════════════
   Security & Extension — Unified page (mb-seu-)
   ══════════════════════════════════════════════ */
.mb-seu-wrap {
    font-family: inherit;
    max-width: 1200px;
    margin: 20px auto 40px;
    padding: 0 15px 40px;
    box-sizing: border-box;
}

/* ── Page header ─────────────────────────────── */
.mb-seu-header {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #f0f4ff 45%, #e8f0fd 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(56,88,233,0.1), 0 0 0 1px #e0e7ff;
    margin-bottom: 22px;
    overflow: hidden;
    min-height: 168px;
    display: flex;
    align-items: stretch;
}
.mb-seu-header-left {
    position: relative;
    z-index: 2;
    padding: 32px 36px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 14px;
    max-width: 500px;
    flex-shrink: 0;
}
.mb-seu-header-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mb-seu-header-icon-box {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3858e9, #6b8af5);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(56,88,233,0.3);
}
.mb-seu-header-right {
    position: absolute;
    inset: 0 0 0 38%;
    overflow: hidden;
    pointer-events: none;
}
.mb-seu-header-text { flex: 1; min-width: 0; }
.mb-seu-header-text h1 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px;
}
.mb-seu-header-text p {
    font-size: 13.5px;
    color: #475569;
    line-height: 1.65;
    margin: 0;
}
.mb-seu-header-illus { display: none; }

/* ── Section container ───────────────────────── */
.mb-seu-section {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 20px;
    overflow: hidden;
}

/* ── Section head ────────────────────────────── */
.mb-seu-section-head {
    padding: 16px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafbfd;
}
.mb-seu-badge {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    flex-shrink: 0;
}
.mb-seu-badge.green   { box-shadow: 0 0 0 5px rgba(22,163,74,0.18),  0 0 0 10px rgba(22,163,74,0.08); }
.mb-seu-badge.blue    { box-shadow: 0 0 0 5px rgba(56,88,233,0.18),  0 0 0 10px rgba(56,88,233,0.08); }
.mb-seu-badge.purple  { box-shadow: 0 0 0 5px rgba(124,58,237,0.18), 0 0 0 10px rgba(124,58,237,0.08); }
.mb-seu-section.accent-green  { border-left: 4px solid #16a34a; }
.mb-seu-section.accent-blue   { border-left: 4px solid #3858e9; }
.mb-seu-section.accent-purple { border-left: 4px solid #7c3aed; }
.mb-seu-section-head-text { flex: 1; min-width: 0; }
.mb-seu-section-head-text h2 {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 2px;
}
.mb-seu-section-head-text p {
    font-size: 12.5px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}
.mb-seu-collapse-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 12px;
    color: #64748b;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
    transition: background 0.15s;
    white-space: nowrap;
}
.mb-seu-collapse-btn:hover { background: #e2e8f0; color: #334155; }

/* ── Section body ────────────────────────────── */
.mb-seu-section-body { padding: 20px 22px; }

/* ── Card grid ───────────────────────────────── */
.mb-seu-grid {
    display: grid;
    gap: 14px;
}
.mb-seu-grid-3 { grid-template-columns: repeat(3, 1fr); }
.mb-seu-grid-4 { grid-template-columns: repeat(4, 1fr); }
.mb-seu-grid-5 { grid-template-columns: repeat(5, 1fr); }

/* ── Feature card ────────────────────────────── */
.mb-seu-feature-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e8edf3;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    transition: all 0.2s;
}
.mb-seu-feature-card:hover {
    box-shadow: 0 4px 14px rgba(0,0,0,0.10);
    border-color: #c7d2fe;
    transform: translateY(-1px);
}
.mb-seu-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.mb-seu-card-title {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.4;
    flex: 1;
}
.mb-seu-card-desc {
    font-size: 12px;
    color: #64748b;
    line-height: 1.55;
    flex: 1;
}

/* ── Card footer: label + toggle ─────────────── */
.mb-seu-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #f1f5f9;
    padding-top: 10px;
    margin-top: auto;
    gap: 8px;
}
.mb-seu-toggle-label {
    font-size: 12px;
    font-weight: 600;
}
.mb-seu-toggle-label.is-on  { color: #16a34a; }
.mb-seu-toggle-label.is-off { color: #94a3b8; }

/* Re-use existing toggle switch */
.mb-wph-switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    flex-shrink: 0;
}
.mb-wph-switch input { display: none; }
.mb-wph-slider {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 24px;
    cursor: pointer;
    transition: background 0.25s;
}
.mb-wph-slider::after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    background: #fff;
    border-radius: 50%;
    left: 3px;
    top: 3px;
    transition: transform 0.25s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.mb-wph-switch input:checked + .mb-wph-slider { background: #22c55e; }
.mb-wph-switch input:checked + .mb-wph-slider::after { transform: translateX(20px); }

/* ── Select inside card ──────────────────────── */
.mb-seu-card-select-wrap {
    border-top: 1px solid #f1f5f9;
    padding-top: 10px;
    margin-top: auto;
}
/* editor segmented control */
.mb-editor-seg {
    display: flex;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    overflow: hidden;
    background: #f8fafc;
}
.mb-editor-seg-opt {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 9px 4px;
    cursor: pointer;
    font-size: 11.5px;
    font-weight: 600;
    color: #94a3b8;
    transition: all .2s;
    line-height: 1.2;
    user-select: none;
}
.mb-editor-seg-opt + .mb-editor-seg-opt { border-left: 1.5px solid #cbd5e1; }
.mb-editor-seg-opt input[type="radio"] { display: none; }
.mb-editor-seg-opt { position: relative; }
.mb-editor-seg-opt.is-active {
    background: #fff;
    color: #3858e9;
    box-shadow: inset 0 1px 4px rgba(56,88,233,0.08);
}
.mb-editor-seg-opt.is-active svg { stroke: #3858e9; }
.mb-editor-seg-opt.is-active::after {
    content: '';
    position: absolute;
    top: 6px; right: 6px;
    width: 14px; height: 14px;
    background: #3858e9;
    border-radius: 50%;
    background-image: url("data:image/svg+xml,%3Csvg width='10' height='10' viewBox='0 0 10 10' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M2 5l2.5 2.5L8 3' stroke='white' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: center;
}

/* ── Extra rows ──────────────────────────────── */
.mb-seu-extra-row {
    background: #f8fafc;
    border-radius: 10px;
    padding: 16px;
    border: 1px dashed #e2e8f0;
    margin-top: 14px;
    display: none;
}
.mb-seu-extra-row.is-visible { display: block; }

/* login url extra — Option A */
#login-url-extra { background: transparent; border: none; padding: 0; }
.mb-lue-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px 22px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    display: flex; flex-direction: column; gap: 14px;
}
.mb-lue-header { display: flex; align-items: flex-start; gap: 13px; }
.mb-lue-icon {
    width: 40px; height: 40px; flex-shrink: 0;
    background: #eff2fe; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.mb-lue-title { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
.mb-lue-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; }
/* domain chip */
.mb-lue-domain-chip {
    display: inline-flex; align-items: center; gap: 7px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 20px; padding: 5px 14px;
    font-size: 12px; color: #64748b; font-weight: 500;
    align-self: flex-start;
}
/* slug input */
.mb-lue-slug-wrap {
    display: flex; align-items: center;
    border: 1.5px solid #cbd5e1; border-radius: 8px;
    overflow: hidden; background: #fff;
    transition: border-color .2s, box-shadow .2s;
}
.mb-lue-slug-wrap:focus-within {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56,88,233,.1);
}
.mb-lue-slash {
    height: 42px; padding: 0 12px;
    display: flex; align-items: center;
    font-size: 16px; font-weight: 700; color: #94a3b8;
    background: #f8fafc; border-right: 1.5px solid #e2e8f0;
    flex-shrink: 0; user-select: none;
}
.mb-lue-slug-input {
    flex: 1; height: 42px; padding: 0 14px;
    border: none !important; outline: none !important; box-shadow: none !important;
    font-size: 13.5px; color: #0f172a; background: #fff;
    font-family: inherit; box-sizing: border-box;
    min-width: 0;
}
.mb-lue-slug-input::placeholder { color: #cbd5e1; }
/* preview row */
.mb-lue-preview-row {
    display: flex; align-items: center; gap: 8px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 8px; padding: 9px 14px;
}
.mb-lue-preview-label { font-size: 12px; color: #94a3b8; font-weight: 500; white-space: nowrap; }
.mb-lue-preview-url { font-size: 12.5px; color: #3858e9; font-weight: 600; word-break: break-all; }
.mb-lue-preview-empty { font-size: 12.5px; color: #cbd5e1; font-style: italic; }
.mb-seu-extra-label {
    display: block; font-size: 13px; font-weight: 600;
    color: #374151; margin-bottom: 8px;
}
.mb-seu-text-input {
    width: 100%;
    padding: 9px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13px;
    color: #1e293b;
    background: #f8fafc;
    box-sizing: border-box;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.mb-seu-text-input:focus {
    border-color: #3858e9;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.12);
}

/* ── Login Theme Extra redesign (mb-lte) ─────── */
#login-theme-extra { background: transparent; border: none; padding: 0; }

/* Header row */
.mb-lte-outer-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    margin-bottom: 16px; gap: 12px;
}
.mb-lte-outer-header-left { display: flex; align-items: flex-start; gap: 12px; }
.mb-lte-outer-icon {
    width: 42px; height: 42px; flex-shrink: 0;
    background: #f3f0ff; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.mb-lte-outer-title { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 3px; }
.mb-lte-outer-desc  { font-size: 12.5px; color: #64748b; }
.mb-lte-configured-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 20px;
    border: 1.5px solid #bbf7d0; background: #f0fdf4;
    font-size: 12px; font-weight: 600; color: #16a34a;
    white-space: nowrap; flex-shrink: 0;
}

/* Main 2-col card */
.mb-lte-card {
    display: flex; align-items: stretch; gap: 0;
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,0.06);
}

/* Col label */
.mb-lte-col-label {
    font-size: 11px; font-weight: 700; color: #94a3b8;
    text-transform: uppercase; letter-spacing: .5px; margin-bottom: 12px;
}

/* Left col: preview + actions */
.mb-lte-left-col {
    flex: 1; display: flex; flex-direction: column;
    padding: 22px; gap: 14px; min-width: 0;
}
.mb-lte-preview-box {
    width: 100%; height: 160px;
    border: 1.5px solid #e8edf3; border-radius: 10px;
    background: #fafafa;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; position: relative;
}
.mb-lte-preview-img { max-width: 85%; max-height: 130px; object-fit: contain; }
.mb-lte-dim-badge {
    display: inline-flex; align-self: flex-start;
    font-size: 11px; color: #94a3b8;
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 20px; padding: 3px 10px; margin-top: -6px;
}
.mb-lte-divider {
    border: none; border-top: 1px dashed #e8edf3; margin: 0;
}
.mb-lte-btn-change {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 11px; width: 100%;
    background: #fff; color: #16a34a;
    border: 1.5px solid #86efac; border-radius: 9px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all .2s; box-shadow: 0 1px 4px rgba(16,185,129,0.1);
}
.mb-lte-btn-change:hover { background: #f0fdf4; border-color: #22c55e; box-shadow: 0 2px 8px rgba(16,185,129,0.18); }
.mb-lte-btn-subtitle { font-size: 11.5px; color: #94a3b8; text-align: center; margin-top: -8px; }
.mb-lte-info-card {
    background: #f8fafc; border: 1px solid #e8edf3;
    border-radius: 9px; padding: 11px 13px;
    display: flex; flex-direction: column; gap: 7px;
}
.mb-lte-info-item {
    display: flex; align-items: center; gap: 9px;
    font-size: 12px; color: #475569;
}
.mb-lte-info-icon {
    width: 22px; height: 22px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-lte-btn-remove {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    padding: 10px; width: 100%;
    background: #fff; color: #ef4444;
    border: 1.5px solid #fca5a5; border-radius: 9px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all .2s;
}
.mb-lte-btn-remove:hover { background: #fff1f2; border-color: #ef4444; }

/* Right col: form preview */
.mb-lte-form-panel {
    flex-shrink: 0; display: flex; flex-direction: column;
    padding: 22px; background: #f8fafc;
    border-left: 1px solid #e8edf3;
    width: 260px; gap: 14px;
}
.mb-lte-form-panel .mb-lte-col-label {
    font-size: 12.5px; font-weight: 700; color: #0f172a;
    text-transform: none; letter-spacing: 0; margin-bottom: 0;
    display: flex; align-items: center; gap: 6px;
}
.mb-lte-form-card {
    background: #fff; border: 1px solid #e8edf3;
    border-radius: 12px; padding: 22px 18px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    display: flex; flex-direction: column; gap: 12px;
}
.mb-lte-form-guide {
    background: #fff; border: 1px solid #e2e8f0;
    border-radius: 10px; padding: 12px 14px;
    display: flex; flex-direction: column; gap: 8px;
}
.mb-lte-form-guide-item {
    display: flex; align-items: flex-start; gap: 8px;
    font-size: 11.5px; color: #64748b; line-height: 1.45;
}
.mb-lte-form-guide-icon {
    width: 20px; height: 20px; border-radius: 5px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}
.mb-lte-form-logo-img {
    max-width: 130px; max-height: 52px; object-fit: contain;
    display: block; margin: 0 auto 6px;
}
.mb-lte-form-field { display: flex; flex-direction: column; gap: 5px; }
.mb-lte-form-label { font-size: 11px; color: #374151; font-weight: 600; }
.mb-lte-form-input-mock {
    height: 34px; border: 1px solid #e2e8f0; border-radius: 7px;
    background: #f9fafb; display: flex; align-items: center;
    padding: 0 10px; gap: 7px;
}
.mb-lte-form-input-mock.mb-lte-input-pw { justify-content: space-between; }
.mb-lte-form-check-row {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; color: #64748b;
}
.mb-lte-form-check-box {
    width: 14px; height: 14px; border: 1.5px solid #d1d5db;
    border-radius: 3px; background: #fff; flex-shrink: 0;
}
.mb-lte-form-login-btn {
    width: 100%; padding: 9px;
    background: #2271b1; color: #fff;
    border: none; border-radius: 7px;
    font-size: 12.5px; font-weight: 700; cursor: not-allowed;
    letter-spacing: .3px;
}

/* URL section below card */
.mb-lte-url-section { margin-top: 20px; padding-top: 18px; border-top: 1px solid #f1f5f9; }
.mb-lte-url-title {
    font-size: 13.5px; font-weight: 700; color: #0f172a; margin-bottom: 3px;
}
.mb-lte-url-title span { font-size: 12px; font-weight: 400; color: #94a3b8; margin-left: 4px; }
.mb-lte-url-desc { font-size: 12px; color: #64748b; margin-bottom: 10px; }
.mb-lte-url-input-wrap {
    display: flex; align-items: center;
    border: 1.5px solid #cbd5e1; border-radius: 8px;
    background: #fff; overflow: hidden;
    transition: border-color .2s, box-shadow .2s;
    margin-bottom: 10px;
}
.mb-lte-url-input-wrap:focus-within {
    border-color: #3858e9; box-shadow: 0 0 0 3px rgba(56,88,233,.1);
}
.mb-lte-url-icon {
    height: 42px; padding: 0 12px;
    display: flex; align-items: center;
    border-right: 1.5px solid #e2e8f0;
    background: #f8fafc; flex-shrink: 0;
}
.mb-seu-url-full-input {
    flex: 1; height: 42px; padding: 0 14px;
    border: none !important; outline: none !important; box-shadow: none !important;
    font-size: 13.5px; color: #0f172a; background: #fff;
    font-family: inherit; box-sizing: border-box; min-width: 0;
}
.mb-lte-new-tab-row {
    display: flex; align-items: center; gap: 8px;
    font-size: 12.5px; color: #475569;
}
.mb-lte-new-tab-row input[type="checkbox"] { margin: 0; width: 14px; height: 14px; cursor: pointer; }
.mb-lte-tooltip-icon {
    width: 16px; height: 16px; border-radius: 50%;
    background: #e2e8f0; color: #94a3b8;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 10px; font-weight: 700; cursor: help;
}

/* ── Save bar ────────────────────────────────── */
.mb-seu-save-bar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 20px;
}
.mb-seu-save-note { font-size: 12.5px; color: #64748b; }
.mb-seu-save-btn {
    background: linear-gradient(135deg, #3858e9, #2563eb);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 11px 32px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(56,88,233,0.35);
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.mb-seu-save-btn:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(56,88,233,0.40);
}

/* ── Responsive ──────────────────────────────── */
@media (max-width: 768px) {
    .mb-seu-grid-3 { grid-template-columns: repeat(2, 1fr); }
    .mb-seu-grid-4 { grid-template-columns: repeat(2, 1fr); }
    .mb-seu-grid-5 { grid-template-columns: repeat(3, 1fr); }
    .mb-seu-header-illus { width: 90px; }
}
@media (max-width: 480px) {
    .mb-seu-grid-3,
    .mb-seu-grid-4,
    .mb-seu-grid-5 { grid-template-columns: 1fr; }
    .mb-seu-header { flex-direction: column; align-items: flex-start; }
    .mb-seu-header-illus { display: none; }
    .mb-seu-save-bar { flex-direction: column; align-items: stretch; }
    .mb-seu-save-btn { justify-content: center; }
}

/* ── 2-column layout ─────────────────────────── */
.mb-seu-layout-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}
.mb-seu-main-col { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.mb-seu-sidebar-col { position: sticky; top: 32px; display: flex; flex-direction: column; gap: 14px; }
@media (max-width: 1100px) { .mb-seu-layout-grid { grid-template-columns: 1fr 300px; } }
@media (max-width: 960px)  { .mb-seu-layout-grid { grid-template-columns: 1fr; } .mb-seu-sidebar-col { position: static; } }

/* ── Sidebar tip card ───────────────────────── */
.mb-seu-tip-card { background:#fff; border-radius:12px; border:1px solid #e8edf3; box-shadow:0 1px 4px rgba(0,0,0,.06); overflow:hidden; }
.mb-seu-tip-card-head { padding:11px 14px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:9px; }
.mb-seu-tip-head-icon { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.mb-seu-tip-card-head strong { font-size:12.5px; font-weight:700; color:#0f172a; }
.mb-seu-tip-list { padding:12px 14px; display:flex; flex-direction:column; gap:8px; }
.mb-seu-tip-item { display:flex; align-items:flex-start; gap:9px; font-size:12px; color:#374151; line-height:1.55; }
.mb-seu-tip-dot { width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px; }
.mb-seu-tip-item b { color:#0f172a; }
.mb-seu-tip-card-foot { padding:10px 14px; border-top:1px solid #f8fafc; background:#fafbfd; }
.mb-seu-tip-card-foot p { font-size:11px; color:#94a3b8; margin:0; line-height:1.5; }
</style>

<form method="post" id="mb-seu-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-seu-wrap">

    <!-- ════════════════ PAGE HEADER ════════════════ -->
    <div class="mb-seu-header">
        <!-- Left: icon + title + desc -->
        <div class="mb-seu-header-left">
            <div class="mb-seu-header-title-row">
                <div class="mb-seu-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2L4 6v6c0 5.5 3.8 10.7 8 12 4.2-1.3 8-6.5 8-12V6L12 2z" fill="#fff" fill-opacity=".9"/><path d="M9 12l2 2 4-4" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Bảo vệ & Tối ưu Website', 'whp'); ?></h1>
            </div>
            <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e('Quản lý các công cụ bảo mật, tối ưu hiệu suất và tùy chỉnh quản trị cho website của bạn.', 'whp'); ?></p>
        </div>
        <!-- Right: illustration -->
        <div class="mb-seu-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="seu_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f0f4ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#edf2ff" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#dde8ff" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="seu_sh" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(56,88,233,0.18)"/>
                    </filter>
                    <filter id="seu_shSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(56,88,233,0.12)"/>
                    </filter>
                </defs>
                <!-- bg -->
                <rect width="680" height="168" fill="url(#seu_hbg)"/>
                <!-- decorative circles -->
                <circle cx="590" cy="20" r="65" fill="#3858e9" fill-opacity=".05"/>
                <circle cx="645" cy="145" r="42" fill="#6366f1" fill-opacity=".05"/>
                <circle cx="340" cy="84" r="100" fill="#818cf8" fill-opacity=".03"/>

                <!-- Main shield (center) -->
                <g filter="url(#seu_sh)">
                    <path d="M415 22l-54 24v38c0 30.8 21.5 59.6 54 66.4 32.5-6.8 54-35.6 54-66.4V46L415 22z" fill="#fff"/>
                    <path d="M415 22l-54 24v38c0 30.8 21.5 59.6 54 66.4 32.5-6.8 54-35.6 54-66.4V46L415 22z" fill="#eff2fe" fill-opacity=".7" stroke="#3858e9" stroke-width="2"/>
                    <circle cx="415" cy="86" r="22" fill="#3858e9" fill-opacity=".12"/>
                    <path d="M404 86l8 8 18-18" stroke="#3858e9" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </g>

                <!-- Lock (right area) -->
                <g filter="url(#seu_shSm)">
                    <rect x="530" y="30" width="48" height="38" rx="6" fill="#fff"/>
                    <rect x="534" y="30" width="40" height="22" rx="4" fill="#dcfce7"/>
                    <path d="M539 30v-8a15 15 0 0 1 30 0v8" stroke="#16a34a" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <circle cx="554" cy="49" r="4" fill="#16a34a"/>
                    <line x1="554" y1="53" x2="554" y2="59" stroke="#16a34a" stroke-width="2" stroke-linecap="round"/>
                </g>

                <!-- Gear (right-bottom) -->
                <g filter="url(#seu_shSm)" transform="translate(582,90)">
                    <circle cx="26" cy="26" r="26" fill="#fff"/>
                    <circle cx="26" cy="26" r="10" fill="none" stroke="#7c3aed" stroke-width="2.5"/>
                    <path d="M26 8v6M26 38v6M8 26h6M38 26h6M12.7 12.7l4.2 4.2M35.1 35.1l4.2 4.2M35.1 16.9l-4.2 4.2M17.3 35.1l-4.2 4.2" stroke="#7c3aed" stroke-width="2" stroke-linecap="round"/>
                </g>

                <!-- Bug/virus (blocked) -->
                <g filter="url(#seu_shSm)" transform="translate(315,30)">
                    <circle cx="26" cy="26" r="26" fill="#fff"/>
                    <circle cx="26" cy="26" r="13" fill="#fee2e2" stroke="#ef4444" stroke-width="1.5"/>
                    <line x1="17" y1="17" x2="35" y2="35" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="35" y1="17" x2="17" y2="35" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round"/>
                </g>

                <!-- Speed meter (performance) -->
                <g filter="url(#seu_shSm)" transform="translate(315,96)">
                    <circle cx="26" cy="26" r="26" fill="#fff"/>
                    <path d="M12 30a14 14 0 0 1 28 0" stroke="#e2e8f0" stroke-width="3" fill="none" stroke-linecap="round"/>
                    <path d="M12 30a14 14 0 0 1 21-12" stroke="#f97316" stroke-width="3" fill="none" stroke-linecap="round"/>
                    <line x1="26" y1="30" x2="34" y2="20" stroke="#0f172a" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="26" cy="30" r="2.5" fill="#0f172a"/>
                </g>

                <!-- Connection dots -->
                <line x1="367" y1="84" x2="415" y2="84" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="4 3"/>
                <line x1="469" y1="84" x2="530" y2="49" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="4 3"/>
                <line x1="469" y1="90" x2="582" y2="116" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="4 3"/>

                <!-- Floating dots -->
                <circle cx="490" cy="28" r="4" fill="#3858e9" fill-opacity=".2"/>
                <circle cx="510" cy="148" r="3.5" fill="#6366f1" fill-opacity=".2"/>
                <circle cx="570" cy="75" r="3" fill="#16a34a" fill-opacity=".3"/>
                <circle cx="650" cy="50" r="5" fill="#818cf8" fill-opacity=".2"/>
                <circle cx="630" cy="130" r="3" fill="#3858e9" fill-opacity=".15"/>
            </svg>
        </div>
    </div>

    <div class="mb-seu-layout-grid">
    <div class="mb-seu-main-col">

    <!-- ════════════ SECTION 1: BẢO VỆ WEBSITE ═════════ -->
    <div class="mb-seu-section accent-green" id="mb-seu-s1">
        <div class="mb-seu-section-head">
            <div class="mb-seu-badge green" style="background:#16a34a;">1</div>
            <div class="mb-seu-section-head-text">
                <h2><?php esc_html_e('Bảo vệ Website', 'whp'); ?></h2>
                <p><?php esc_html_e('Tăng cường bảo mật và bảo vệ website khỏi các truy cập không mong muốn.', 'whp'); ?></p>
            </div>
            <button type="button" class="mb-seu-collapse-btn" data-target="mb-seu-s1-body">
                <span><?php esc_html_e('Thu gọn', 'whp'); ?></span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
        </div>
        <div class="mb-seu-section-body" id="mb-seu-s1-body">
            <div class="mb-seu-grid mb-seu-grid-3">

                <!-- 1. XML-RPC -->
                <div class="mb-seu-feature-card" id="card-whp_security_remove_xmlrpc">
                    <div class="mb-seu-card-icon" style="background:#ccfbf1;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Vô hiệu hóa XML-RPC', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Hạn chế các cuộc tấn công dò mật khẩu và làm quá tải hệ thống qua giao thức XML-RPC.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_security_remove_xmlrpc) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_security_remove_xmlrpc) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_security_remove_xmlrpc"
                                   <?php checked(mb_seu_on($whp_security_remove_xmlrpc)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 2. Cấm sao chép -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#fef3c7;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/><line x1="3" y1="3" x2="21" y2="21"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Cấm sao chép nội dung', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Tắt chuột phải và xem mã nguồn trang — khách truy cập không thể sao chép nội dung.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_security_disable_copy) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_security_disable_copy) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_security_disable_copy"
                                   <?php checked(mb_seu_on($whp_security_disable_copy)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 3. Xóa wp_head links -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#dcfce7;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="19" y1="12" x2="5" y2="12"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Xóa các liên kết từ wp_head', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Xóa các liên kết không cần thiết trong <head> giúp tải nhanh hơn và SEO tốt hơn.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_security_delete_wphead) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_security_delete_wphead) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_security_delete_wphead"
                                   <?php checked(mb_seu_on($whp_security_delete_wphead)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 4. Ẩn WP version -->
                <div class="mb-seu-feature-card" id="card-whp_security_hide_wp_version">
                    <div class="mb-seu-card-icon" style="background:#ede9fe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Ẩn phiên bản WordPress', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Ẩn thông tin phiên bản WordPress khỏi mã HTML của website, tránh lộ thông tin cho hacker.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_security_hide_wp_version) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_security_hide_wp_version) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_security_hide_wp_version"
                                   <?php checked(mb_seu_on($whp_security_hide_wp_version)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 5. Ẩn menu Theme/Plugin -->
                <div class="mb-seu-feature-card" id="card-whp_security_hide_theme_plugin">
                    <div class="mb-seu-card-icon" style="background:#ffe4e6;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Ẩn menu Theme / Plugin', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Tắt chức năng chỉnh sửa theme và plugin trực tiếp trong trang quản trị WordPress.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_security_hide_theme_plugin) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_security_hide_theme_plugin) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_security_hide_theme_plugin"
                                   <?php checked(mb_seu_on($whp_security_hide_theme_plugin)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 6. Thay đổi login URL -->
                <div class="mb-seu-feature-card" id="card-whp_security_change_login_url">
                    <div class="mb-seu-card-icon" style="background:#dbeafe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Thay đổi đường dẫn đăng nhập', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Thay URL /wp-login.php bằng đường dẫn tùy chỉnh để tránh tấn công dò mật khẩu.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <?php if (mb_seu_on($whp_security_change_login_url) && !empty($whp_new_login_url)): ?>
                        <button type="button" class="mb-seu-cfg-btn" data-popup="popup-login-url">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <?php esc_html_e('Cấu hình', 'whp'); ?>
                        </button>
                        <?php else: ?>
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_security_change_login_url) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_security_change_login_url) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <?php endif; ?>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_security_change_login_url"
                                   id="login-url-toggle"
                                   <?php checked(mb_seu_on($whp_security_change_login_url)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

            </div><!-- /.mb-seu-grid -->

        </div><!-- /#mb-seu-s1-body -->
    </div><!-- /#mb-seu-s1 -->

    <!-- ════════════ SECTION 2: TỐI ƯU WEBSITE ════════ -->
    <div class="mb-seu-section accent-blue" id="mb-seu-s2">
        <div class="mb-seu-section-head">
            <div class="mb-seu-badge blue" style="background:#3858e9;">2</div>
            <div class="mb-seu-section-head-text">
                <h2><?php esc_html_e('Tối ưu Website', 'whp'); ?></h2>
                <p><?php esc_html_e('Các công cụ giúp tăng tốc và cải thiện trải nghiệm sử dụng website.', 'whp'); ?></p>
            </div>
            <button type="button" class="mb-seu-collapse-btn" data-target="mb-seu-s2-body">
                <span><?php esc_html_e('Thu gọn', 'whp'); ?></span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
        </div>
        <div class="mb-seu-section-body" id="mb-seu-s2-body">
            <div class="mb-seu-grid mb-seu-grid-4">

                <!-- 1. Editor type (SEGMENTED CONTROL) -->
                <?php $editorVal = (string)$whp_extention_editor_type; ?>
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#eff2fe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Trình soạn thảo văn bản', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Chọn trình soạn thảo mặc định cho trang và bài viết.', 'whp'); ?></div>
                    <div class="mb-seu-card-select-wrap">
                        <div class="mb-editor-seg">
                            <!-- Gutenberg -->
                            <label class="mb-editor-seg-opt <?php echo ($editorVal === '0' || $editorVal === '') ? 'is-active' : ''; ?>">
                                <input type="radio" name="whp_extention_editor_type" value="0"
                                       <?php checked($editorVal === '0' || $editorVal === '', true); ?>>
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="8" height="8" rx="1.5"/>
                                    <rect x="13" y="3" width="8" height="8" rx="1.5"/>
                                    <rect x="3" y="13" width="8" height="8" rx="1.5"/>
                                    <rect x="13" y="13" width="8" height="8" rx="1.5"/>
                                </svg>
                                Gutenberg
                            </label>
                            <!-- Classic -->
                            <label class="mb-editor-seg-opt <?php echo $editorVal === '1' ? 'is-active' : ''; ?>">
                                <input type="radio" name="whp_extention_editor_type" value="1"
                                       <?php checked($editorVal, '1'); ?>>
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="4" y1="6" x2="20" y2="6"/>
                                    <line x1="4" y1="10" x2="20" y2="10"/>
                                    <line x1="4" y1="14" x2="14" y2="14"/>
                                    <path d="M17 17l3 3m0 0l-3 3m3-3H13"/>
                                </svg>
                                Classic
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. Nhân bản trang/bài viết -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#fff4ed;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Nhân bản trang / bài viết', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Cho phép tạo bản sao của trang hoặc bài viết với một cú nhấp chuột.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_duplicate_page_post) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_duplicate_page_post) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_duplicate_page_post"
                                   <?php checked(mb_seu_on($whp_extention_duplicate_page_post)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 3. Nhân bản menu -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#e0f2fe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/><circle cx="21" cy="6" r="2" fill="#0891b2" stroke="none"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Nhân bản menu', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Tạo bản sao của menu điều hướng hiện có để tái sử dụng và chỉnh sửa dễ dàng hơn.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_duplicate_menu) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_duplicate_menu) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_duplicate_menu"
                                   <?php checked(mb_seu_on($whp_extention_duplicate_menu)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 4. Chuyển 404 về trang chủ -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#d1fae5;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Chuyển 404 về trang chủ', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Tự động chuyển hướng về trang chủ khi khách truy cập gặp lỗi 404.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_enable_404_redirect) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_enable_404_redirect) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_enable_404_redirect"
                                   <?php checked(mb_seu_on($whp_extention_enable_404_redirect)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 5. Xóa Emojis -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#ffe4e6;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 15s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Xóa biểu tượng Emojis', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Không tải file wp-emoji-release.min.js — giảm số request HTTP không cần thiết.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_disable_emojis) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_disable_emojis) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_disable_emojis"
                                   <?php checked(mb_seu_on($whp_extention_disable_emojis)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 6. Remove Query Strings -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#e0e7ff;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <div class="mb-seu-card-title">Remove Query Strings</div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Xóa chuỗi truy vấn (?ver=x.x) khỏi tài nguyên tĩnh để tối ưu cache trình duyệt.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_remove_query_string) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_remove_query_string) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_remove_query_string"
                                   <?php checked(mb_seu_on($whp_extention_remove_query_string)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 7. Disable WP Embeds -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#f1f5f9;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div class="mb-seu-card-title">Disable WordPress Embeds</div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Tắt tính năng chèn mã nhúng oEmbeds trong WordPress để giảm tải không cần thiết.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_disbale_wp_embeds) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_disbale_wp_embeds) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_disbale_wp_embeds"
                                   <?php checked(mb_seu_on($whp_extention_disbale_wp_embeds)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 8. Tắt Google Fonts -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#fef3c7;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Tắt Google Fonts', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Không tải Google Fonts từ server bên ngoài — dùng font mặc định của hệ thống thay thế.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_disbale_google_fonts) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_disbale_google_fonts) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_disbale_google_fonts"
                                   <?php checked(mb_seu_on($whp_extention_disbale_google_fonts)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 9. Tắt Heartbeat Frontend -->
                <?php $whp_extention_disable_heartbeat_frontend = $option['whp_extention_disable_heartbeat_frontend'] ?? ''; ?>
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#fce7f3;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#db2777" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Tắt Heartbeat Frontend', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Xóa WP Heartbeat khỏi trang công khai — giảm lượt gọi', 'whp'); ?> <code>admin-ajax.php</code> <?php esc_html_e('cho khách truy cập.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_disable_heartbeat_frontend) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_disable_heartbeat_frontend) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_disable_heartbeat_frontend"
                                   <?php checked(mb_seu_on($whp_extention_disable_heartbeat_frontend)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 10. Giảm tần suất Heartbeat Admin -->
                <?php $whp_extention_heartbeat_limit_admin = $option['whp_extention_heartbeat_limit_admin'] ?? ''; ?>
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#ede9fe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Giảm tần suất Heartbeat Admin', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Tăng khoảng cách gọi Heartbeat trong trang quản trị từ', 'whp'); ?> <strong>15s → 60s</strong> — <?php esc_html_e('giảm 75% số lượt gọi', 'whp'); ?> <code>admin-ajax.php</code>.</div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_heartbeat_limit_admin) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_heartbeat_limit_admin) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_heartbeat_limit_admin"
                                   <?php checked(mb_seu_on($whp_extention_heartbeat_limit_admin)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

            </div><!-- /.mb-seu-grid -->
        </div><!-- /#mb-seu-s2-body -->
    </div><!-- /#mb-seu-s2 -->

    <!-- ══════════ SECTION 3: QUẢN TRỊ HỆ THỐNG ════════ -->
    <div class="mb-seu-section accent-purple" id="mb-seu-s3">
        <div class="mb-seu-section-head">
            <div class="mb-seu-badge purple" style="background:#7c3aed;">3</div>
            <div class="mb-seu-section-head-text">
                <h2><?php esc_html_e('Quản trị hệ thống', 'whp'); ?></h2>
                <p><?php esc_html_e('Tùy chỉnh các tính năng hỗ trợ quản lý website và WooCommerce.', 'whp'); ?></p>
            </div>
            <button type="button" class="mb-seu-collapse-btn" data-target="mb-seu-s3-body">
                <span><?php esc_html_e('Thu gọn', 'whp'); ?></span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
        </div>
        <div class="mb-seu-section-body" id="mb-seu-s3-body">
            <div class="mb-seu-grid mb-seu-grid-4">

                <!-- 1. Tắt thông báo -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#fff4ed;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Tắt thông báo', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Ẩn tất cả thông báo cập nhật và admin notices trong trang quản trị.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_notification) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_notification) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_notification"
                                   <?php checked(mb_seu_on($whp_extention_notification)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 2. Tắt Dashicons -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#eff2fe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Tắt Dashicons', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Không tải Dashicons cho người dùng chưa đăng nhập — giảm tải CSS không cần thiết.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_disbale_dashicons) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_disbale_dashicons) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_disbale_dashicons"
                                   <?php checked(mb_seu_on($whp_extention_disbale_dashicons)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 3. Giao diện đăng nhập (with extra) -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#ede9fe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 21v-1a5 5 0 0 1 10 0v1"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Giao diện đăng nhập', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Thay logo WordPress mặc định bằng logo của bạn trên trang đăng nhập.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <?php
                        $login_theme_configured = mb_seu_on($whp_extention_custom_login_theme)
                            && $whp_extention_custom_login_logo
                            && strpos($whp_extention_custom_login_logo, 'assets/admin/images/icon.svg') === false;
                        ?>
                        <?php if ($login_theme_configured): ?>
                        <button type="button" class="mb-seu-cfg-btn" data-popup="popup-login-theme">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            <?php esc_html_e('Cấu hình', 'whp'); ?>
                        </button>
                        <?php else: ?>
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_custom_login_theme) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_custom_login_theme) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <?php endif; ?>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_custom_login_theme"
                                   id="login-theme-toggle"
                                   <?php checked(mb_seu_on($whp_extention_custom_login_theme)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

                <!-- 4. Upload SVG -->
                <div class="mb-seu-feature-card">
                    <div class="mb-seu-card-icon" style="background:#ffe4e6;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e11d48" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                    </div>
                    <div class="mb-seu-card-title"><?php esc_html_e('Cho phép upload SVG', 'whp'); ?></div>
                    <div class="mb-seu-card-desc"><?php esc_html_e('Bật hỗ trợ upload file SVG vào thư viện media của WordPress.', 'whp'); ?></div>
                    <div class="mb-seu-card-footer">
                        <span class="mb-seu-toggle-label <?php echo mb_seu_on($whp_extention_svg) ? 'is-on' : 'is-off'; ?>">
                            <?php echo mb_seu_on($whp_extention_svg) ? esc_html__('Bật', 'whp') : esc_html__('Tắt', 'whp'); ?>
                        </span>
                        <label class="mb-wph-switch">
                            <input type="checkbox" name="whp_extention_svg"
                                   <?php checked(mb_seu_on($whp_extention_svg)); ?>>
                            <span class="mb-wph-slider"></span>
                        </label>
                    </div>
                </div>

            </div><!-- /.mb-seu-grid -->


        </div><!-- /#mb-seu-s3-body -->
    </div><!-- /#mb-seu-s3 -->

    </div><!-- /.mb-seu-main-col -->

    <!-- ════════════ SIDEBAR ════════════ -->
    <div class="mb-seu-sidebar-col">

        <!-- Hướng dẫn & Mẹo sử dụng -->
        <div style="background:#fff;border-radius:12px;box-shadow:0 1px 4px rgba(0,0,0,0.07),0 0 0 1px #e8edf3;overflow:hidden;">
            <div style="padding:18px 20px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h3 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;"><?php esc_html_e('Hướng dẫn & Mẹo sử dụng', 'whp'); ?></h3>
                        <p style="margin:0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Bảo vệ & tối ưu hiệu quả', 'whp'); ?></p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">

                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Bật XML-RPC + Ẩn phiên bản WP', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Hai tùy chọn bảo mật cơ bản nhất — nên bật ngay khi cài đặt plugin.', 'whp'); ?></span>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#1e3a8a;display:block;margin-bottom:1px;"><?php esc_html_e('Query Strings + Emoji → bật cùng lúc', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#2563eb;line-height:1.4;display:block;"><?php esc_html_e('Kết hợp hai tùy chọn này tăng điểm PageSpeed và Core Web Vitals đáng kể.', 'whp'); ?></span>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#581c87;display:block;margin-bottom:1px;"><?php esc_html_e('Classic Editor cho Flatsome & Elementor', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#7c3aed;line-height:1.4;display:block;"><?php esc_html_e('Bật Classic Editor nếu đang dùng page builder — tránh xung đột với Gutenberg.', 'whp'); ?></span>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="#fff" stroke-width="2"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#7c2d12;display:block;margin-bottom:1px;"><?php esc_html_e('SVG chỉ bật nếu nguồn tin cậy', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#c2410c;line-height:1.4;display:block;"><?php esc_html_e('File SVG có thể chứa script độc hại. Chỉ cho phép upload từ admin hoặc nguồn kiểm soát được.', 'whp'); ?></span>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Redirect 404 giữ khách ở lại', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#15803d;line-height:1.4;display:block;"><?php esc_html_e('Tự động chuyển trang lỗi về trang chủ — giảm tỷ lệ thoát và cải thiện trải nghiệm người dùng.', 'whp'); ?></span>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#64748b;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="2"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#374151;display:block;margin-bottom:1px;"><?php esc_html_e('Đổi URL login → lưu URL mới ngay', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#64748b;line-height:1.4;display:block;"><?php esc_html_e('Sau khi đổi slug đăng nhập, bookmark URL mới lại để tránh bị khóa khỏi trang quản trị.', 'whp'); ?></span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div><!-- /.mb-seu-sidebar-col -->

    </div><!-- /.mb-seu-layout-grid -->

    <!-- ════════════════ SAVE BAR (full width) ════════════════ -->
    <div class="mb-seu-save-bar">
        <span class="mb-seu-save-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            <?php esc_html_e('Các thay đổi sẽ áp dụng ngay sau khi lưu', 'whp'); ?>
        </span>
        <button type="submit" name="submit" class="mb-seu-save-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?php esc_html_e('Lưu thông tin', 'whp'); ?>
        </button>
    </div>

</div><!-- /.mb-seu-wrap -->

<style>
/* ── Popup overlay ── */
.mb-popup-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.55);
    z-index: 100000;
    align-items: center;
    justify-content: center;
}
.mb-popup-overlay.is-open { display: flex; }
.mb-popup-box {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    width: 640px;
    max-width: 95vw;
    max-height: 90vh;
    overflow-y: auto;
    animation: mb-popup-in 0.22s ease;
}
@keyframes mb-popup-in {
    from { opacity:0; transform: scale(0.94) translateY(12px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
}
.mb-popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-popup-header h3 { margin:0; font-size:16px; font-weight:700; color:#0f172a; }
.mb-popup-close {
    width: 30px; height: 30px;
    border: none; background: #f1f5f9;
    border-radius: 8px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; color: #64748b;
    transition: background 0.15s;
}
.mb-popup-close:hover { background: #e2e8f0; color: #0f172a; }
.mb-popup-body { padding: 24px; }
.mb-popup-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px;
    border-top: 1px solid #f1f5f9;
}
.mb-popup-btn-cancel {
    padding: 9px 20px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: background 0.15s;
}
.mb-popup-btn-cancel:hover { background: #e2e8f0; }
.mb-popup-btn-save {
    padding: 9px 24px;
    background: linear-gradient(135deg, #3858e9, #2563eb);
    border: none;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(56,88,233,0.3);
    transition: all 0.15s;
}
.mb-popup-btn-save:hover { opacity: 0.9; transform: translateY(-1px); }
/* Config button on card */
.mb-seu-cfg-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    background: #eff2fe;
    border: 1px solid #c7d2fe;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
    color: #3858e9;
    cursor: pointer;
    transition: background 0.15s;
}
.mb-seu-cfg-btn:hover { background: #e0e7ff; }
</style>

<!-- ── Popup: Thay đổi đường dẫn đăng nhập ── -->
<div class="mb-popup-overlay" id="popup-login-url">
    <div class="mb-popup-box">
        <div class="mb-popup-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                <?php esc_html_e('Thay đổi đường dẫn đăng nhập', 'whp'); ?>
            </h3>
            <button type="button" class="mb-popup-close" data-close-popup="popup-login-url">&times;</button>
        </div>
        <div class="mb-popup-body">
            <div class="mb-lue-card" style="box-shadow:none;padding:0;">
                <div class="mb-lue-header">
                    <div class="mb-lue-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <div class="mb-lue-title"><?php esc_html_e('URL đăng nhập tùy chỉnh', 'whp'); ?></div>
                        <div class="mb-lue-desc"><?php esc_html_e('Thay thế', 'whp'); ?> <code>/wp-login.php</code> <?php esc_html_e('bằng slug tùy chỉnh để ẩn trang đăng nhập mặc định, giảm rủi ro brute-force.', 'whp'); ?></div>
                    </div>
                </div>
                <div class="mb-lue-domain-chip">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <?php echo esc_html(get_site_url()); ?>
                </div>
                <div class="mb-lue-slug-wrap">
                    <span class="mb-lue-slash">/</span>
                    <input type="text" class="mb-lue-slug-input" id="mb-lue-slug-input"
                           name="whp_new_login_url"
                           placeholder="dangnhap"
                           value="<?php echo esc_attr($whp_new_login_url); ?>">
                </div>
                <div class="mb-lue-preview-row">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span class="mb-lue-preview-label"><?php esc_html_e('URL đầy đủ:', 'whp'); ?></span>
                    <span id="mb-lue-preview-url"
                          class="<?php echo $whp_new_login_url ? 'mb-lue-preview-url' : 'mb-lue-preview-empty'; ?>">
                        <?php echo $whp_new_login_url
                            ? esc_html(get_site_url() . '/' . $whp_new_login_url)
                            : esc_html__('Nhập slug để xem trước…', 'whp'); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="mb-popup-footer">
            <button type="button" class="mb-popup-btn-cancel" data-close-popup="popup-login-url"><?php esc_html_e('Đóng', 'whp'); ?></button>
            <button type="submit" name="submit" class="mb-popup-btn-save">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:5px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?php esc_html_e('Lưu cài đặt', 'whp'); ?>
            </button>
        </div>
    </div>
</div>

<!-- ── Popup: Giao diện đăng nhập ── -->
<div class="mb-popup-overlay" id="popup-login-theme">
    <div class="mb-popup-box" style="width:760px;">
        <div class="mb-popup-header">
            <h3>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2" style="vertical-align:middle;margin-right:6px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="10" r="3"/><path d="M7 21v-1a5 5 0 0 1 10 0v1"/></svg>
                <?php esc_html_e('Giao diện đăng nhập', 'whp'); ?>
            </h3>
            <button type="button" class="mb-popup-close" data-close-popup="popup-login-theme">&times;</button>
        </div>
        <div class="mb-popup-body">
            <!-- 2-col card -->
            <div class="mb-lte-card">

                <!-- Col trái: Logo preview + Actions -->
                <div class="mb-lte-left-col">
                    <div class="mb-lte-col-label"><?php esc_html_e('Logo hiện tại', 'whp'); ?></div>

                    <!-- Preview box -->
                    <div class="mb-lte-preview-box">
                        <img class="mb-lte-preview-img"
                             src="<?php echo esc_url($whp_extention_custom_login_logo); ?>"
                             alt="<?php esc_attr_e('Logo xem trước', 'whp'); ?>">
                    </div>
                    <div class="mb-lte-dim-badge" id="mb-lte-dim">300 × 80px</div>

                    <!-- Divider -->
                    <hr class="mb-lte-divider">

                    <!-- Upload button -->
                    <button type="button" class="mb-lte-btn-change" id="uploadLogo">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <?php esc_html_e('Thay đổi logo', 'whp'); ?>
                    </button>
                    <div class="mb-lte-btn-subtitle"><?php esc_html_e('Chọn file từ thư viện media', 'whp'); ?></div>

                    <!-- Specs -->
                    <div class="mb-lte-info-card">
                        <div class="mb-lte-info-item">
                            <div class="mb-lte-info-icon" style="background:#dbeafe;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                            </div>
                            <?php esc_html_e('PNG, JPG, WebP', 'whp'); ?>
                        </div>
                        <div class="mb-lte-info-item">
                            <div class="mb-lte-info-icon" style="background:#dcfce7;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                            </div>
                            <?php esc_html_e('Kích thước đề xuất: 300×80px', 'whp'); ?>
                        </div>
                        <div class="mb-lte-info-item">
                            <div class="mb-lte-info-icon" style="background:#ede9fe;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                            </div>
                            <?php esc_html_e('Dung lượng tối đa: 2MB', 'whp'); ?>
                        </div>
                    </div>

                    <!-- Remove button -->
                    <button type="button" class="mb-lte-btn-remove" id="removeLogo"
                            data-default="<?php echo esc_url(MB_WHP_URL . 'assets/admin/images/icon.svg'); ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                        <?php esc_html_e('Xóa logo hiện tại', 'whp'); ?>
                    </button>
                </div>

                <!-- Col phải: Form preview -->
                <div class="mb-lte-form-panel">
                    <div class="mb-lte-col-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                        <?php esc_html_e('Xem trước trang đăng nhập', 'whp'); ?>
                    </div>
                    <div class="mb-lte-form-card">
                        <img class="mb-lte-form-logo-img"
                             src="<?php echo esc_url($whp_extention_custom_login_logo); ?>"
                             alt="Logo">
                        <div class="mb-lte-form-field">
                            <div class="mb-lte-form-label"><?php esc_html_e('Tên người dùng hoặc Email', 'whp'); ?></div>
                            <div class="mb-lte-form-input-mock">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                        </div>
                        <div class="mb-lte-form-field">
                            <div class="mb-lte-form-label"><?php esc_html_e('Mật khẩu', 'whp'); ?></div>
                            <div class="mb-lte-form-input-mock mb-lte-input-pw">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </div>
                        </div>
                        <div class="mb-lte-form-check-row">
                            <div class="mb-lte-form-check-box"></div>
                            <span><?php esc_html_e('Ghi nhớ đăng nhập', 'whp'); ?></span>
                        </div>
                        <button type="button" class="mb-lte-form-login-btn" disabled><?php esc_html_e('Đăng nhập', 'whp'); ?></button>
                    </div>

                    <!-- Hướng dẫn -->
                    <div class="mb-lte-form-guide">
                        <div class="mb-lte-form-guide-item">
                            <div class="mb-lte-form-guide-icon" style="background:#dbeafe;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                            </div>
                            <span><?php esc_html_e('Preview cập nhật ngay khi bạn đổi logo bên trái.', 'whp'); ?></span>
                        </div>
                        <div class="mb-lte-form-guide-item">
                            <div class="mb-lte-form-guide-icon" style="background:#dcfce7;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                            </div>
                            <span><?php esc_html_e('Logo đề xuất', 'whp'); ?> <strong>300×80px</strong> <?php esc_html_e('để hiển thị sắc nét.', 'whp'); ?></span>
                        </div>
                        <div class="mb-lte-form-guide-item">
                            <div class="mb-lte-form-guide-icon" style="background:#fef3c7;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 10c-.83 0-1.5-.67-1.5-1.5v-5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5z"/><path d="M20.5 10H19V8.5c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/><path d="M9.5 14c.83 0 1.5.67 1.5 1.5v5c0 .83-.67 1.5-1.5 1.5S8 21.33 8 20.5v-5c0-.83.67-1.5 1.5-1.5z"/><path d="M3.5 14H5v1.5c0 .83-.67 1.5-1.5 1.5S2 16.33 2 15.5 2.67 14 3.5 14z"/><path d="M14 14.5c0-.83.67-1.5 1.5-1.5h5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-5c-.83 0-1.5-.67-1.5-1.5z"/><path d="M15.5 19H14v1.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"/><path d="M10 9.5C10 8.67 9.33 8 8.5 8h-5C2.67 8 2 8.67 2 9.5S2.67 11 3.5 11h5c.83 0 1.5-.67 1.5-1.5z"/><path d="M8.5 5H10V3.5C10 2.67 9.33 2 8.5 2S7 2.67 7 3.5 7.67 5 8.5 5z"/></svg>
                            </div>
                            <span><?php esc_html_e('Hiển thị tại', 'whp'); ?> <strong>wp-login.php</strong> <?php esc_html_e('cho mọi khách truy cập.', 'whp'); ?></span>
                        </div>
                    </div>
                </div>

            </div><!-- /.mb-lte-card -->

            <!-- URL section -->
            <div class="mb-lte-url-section">
                <div class="mb-lte-url-title">
                    <?php esc_html_e('Đường dẫn liên kết khi nhấp logo', 'whp'); ?>
                    <span>(<?php esc_html_e('tùy chọn', 'whp'); ?>)</span>
                </div>
                <div class="mb-lte-url-desc"><?php esc_html_e('Nhập URL sẽ được mở khi người dùng nhấp vào logo ở trang đăng nhập.', 'whp'); ?></div>
                <div class="mb-lte-url-input-wrap">
                    <div class="mb-lte-url-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <input type="text" class="mb-seu-url-full-input" name="whp_extention_custom_link"
                           placeholder="https://matbao.net"
                           value="<?php echo esc_attr($whp_extention_custom_link); ?>">
                </div>
                <div class="mb-lte-new-tab-row">
                    <input type="checkbox" name="whp_extention_custom_link_new_tab" id="lte-new-tab"
                           <?php checked(mb_seu_on($whp_extention_custom_link_new_tab)); ?>>
                    <label for="lte-new-tab"><?php esc_html_e('Mở liên kết ở tab mới', 'whp'); ?></label>
                    <span class="mb-lte-tooltip-icon" title="<?php esc_attr_e('Khi bật, logo sẽ mở URL trong tab trình duyệt mới (target=_blank)', 'whp'); ?>">?</span>
                </div>
            </div>

            <input type="hidden" name="whp_extention_custom_login_logo"
                   value="<?php echo esc_attr($whp_extention_custom_login_logo); ?>">
        </div>
        <div class="mb-popup-footer">
            <button type="button" class="mb-popup-btn-cancel" data-close-popup="popup-login-theme"><?php esc_html_e('Đóng', 'whp'); ?></button>
            <button type="submit" name="submit" class="mb-popup-btn-save">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:5px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <?php esc_html_e('Lưu cài đặt', 'whp'); ?>
            </button>
        </div>
    </div>
</div>

</form>

<script>
var whpExtI18n = {
    on:          '<?php echo esc_js(__("Bật","whp")); ?>',
    off:         '<?php echo esc_js(__("Tắt","whp")); ?>',
    collapse:    '<?php echo esc_js(__("Thu gọn","whp")); ?>',
    expand:      '<?php echo esc_js(__("Mở rộng","whp")); ?>',
    slugHint:    '<?php echo esc_js(__("Nhập slug để xem trước…","whp")); ?>'
};
(function () {
    'use strict';

    /* ── Collapse / Expand sections ──────────────────── */
    document.querySelectorAll('.mb-seu-collapse-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = btn.getAttribute('data-target');
            var body = document.getElementById(targetId);
            var span = btn.querySelector('span');
            var svg  = btn.querySelector('svg');
            if (!body) return;
            var collapsed = body.style.display === 'none';
            if (collapsed) {
                body.style.display = '';
                span.textContent = whpExtI18n.collapse;
                svg.style.transform = '';
            } else {
                body.style.display = 'none';
                span.textContent = whpExtI18n.expand;
                svg.style.transform = 'rotate(180deg)';
            }
        });
    });

    /* ── Editor segmented control ───────────────────── */
    document.querySelectorAll('.mb-editor-seg-opt input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            var seg = radio.closest('.mb-editor-seg');
            if (!seg) return;
            seg.querySelectorAll('.mb-editor-seg-opt').forEach(function (opt) {
                opt.classList.remove('is-active');
            });
            radio.closest('.mb-editor-seg-opt').classList.add('is-active');
        });
    });

    /* ── Login URL live preview ──────────────────────── */
    (function () {
        var slugInput  = document.getElementById('mb-lue-slug-input');
        var previewEl  = document.getElementById('mb-lue-preview-url');
        var baseUrl    = '<?php echo esc_js(get_site_url()); ?>/';
        if (!slugInput || !previewEl) return;
        function updatePreview() {
            var slug = slugInput.value.trim();
            if (slug) {
                previewEl.textContent = baseUrl + slug;
                previewEl.className   = 'mb-lue-preview-url';
            } else {
                previewEl.textContent = whpExtI18n.slugHint;
                previewEl.className   = 'mb-lue-preview-empty';
            }
        }
        slugInput.addEventListener('input', updatePreview);
    }());

    /* ── Toggle label sync ───────────────────────────── */
    function syncLabel(input) {
        var footer = input.closest('.mb-seu-card-footer');
        if (!footer) return;
        var label = footer.querySelector('.mb-seu-toggle-label');
        if (!label) return;
        if (input.checked) {
            label.textContent = whpExtI18n.on;
            label.classList.add('is-on');
            label.classList.remove('is-off');
        } else {
            label.textContent = whpExtI18n.off;
            label.classList.remove('is-on');
            label.classList.add('is-off');
        }
    }

    document.querySelectorAll('.mb-seu-feature-card .mb-wph-switch input[type="checkbox"]').forEach(function (input) {
        // Initial state already set via PHP class, but re-sync for safety
        syncLabel(input);
        input.addEventListener('change', function () {
            syncLabel(input);
        });
    });

    /* ── Popup open/close ───────────────────────────── */
    function mbOpenPopup(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.add('is-open'); }
    }
    function mbClosePopup(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.remove('is-open'); }
    }

    // Close buttons
    document.querySelectorAll('[data-close-popup]').forEach(function(btn) {
        btn.addEventListener('click', function() { mbClosePopup(this.dataset.closePopup); });
    });

    // Config buttons on cards
    document.querySelectorAll('[data-popup]').forEach(function(btn) {
        btn.addEventListener('click', function() { mbOpenPopup(this.dataset.popup); });
    });

    // Close overlay on backdrop click
    document.querySelectorAll('.mb-popup-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) { mbClosePopup(this.id); }
        });
    });

    // Toggle ON → open popup; Toggle OFF → just toggle (no popup)
    var loginUrlToggle = document.getElementById('login-url-toggle');
    if (loginUrlToggle) {
        loginUrlToggle.addEventListener('change', function() {
            if (this.checked) { mbOpenPopup('popup-login-url'); }
        });
    }

    var loginThemeToggle = document.getElementById('login-theme-toggle');
    if (loginThemeToggle) {
        loginThemeToggle.addEventListener('change', function() {
            if (this.checked) { mbOpenPopup('popup-login-theme'); }
        });
    }

    /* Upload/remove handled by app.js — no duplicate handler needed */

}());
</script>

<style>
@keyframes mb-card-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(220,38,38,0.5), 0 4px 15px rgba(220,38,38,0.15); background: rgba(220,38,38,0.06); }
    50%  { box-shadow: 0 0 0 8px rgba(220,38,38,0), 0 4px 20px rgba(220,38,38,0.25); background: rgba(220,38,38,0.12); }
    100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.5), 0 4px 15px rgba(220,38,38,0.15); background: rgba(220,38,38,0.06); }
}
.mb-seu-highlight-card {
    outline: 2px solid #dc2626 !important;
    outline-offset: 2px;
    animation: mb-card-pulse 0.9s ease-in-out 6;
    border-radius: 12px;
    position: relative;
    z-index: 2;
}
@keyframes mb-section-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(56,88,233,0.45), 0 4px 24px rgba(56,88,233,0.12); }
    50%  { box-shadow: 0 0 0 10px rgba(56,88,233,0), 0 6px 32px rgba(56,88,233,0.28); }
    100% { box-shadow: 0 0 0 0 rgba(56,88,233,0.45), 0 4px 24px rgba(56,88,233,0.12); }
}
.mb-seu-highlight-section {
    outline: 2.5px solid #3858e9 !important;
    outline-offset: 3px;
    animation: mb-section-pulse 0.85s ease-in-out 6;
    border-radius: 14px;
    position: relative;
    z-index: 2;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash;
    if (!hash) return;
    var target = document.querySelector(hash);
    if (!target) return;
    var isSection = hash.startsWith('#mb-seu-s');
    var cls = isSection ? 'mb-seu-highlight-section' : 'mb-seu-highlight-card';
    var duration = isSection ? 5100 : 6000;
    setTimeout(function() {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        target.classList.add(cls);
        setTimeout(function() { target.classList.remove(cls); }, duration);
    }, 400);
});
</script>
<?php whp_get_shared('footer'); ?>
