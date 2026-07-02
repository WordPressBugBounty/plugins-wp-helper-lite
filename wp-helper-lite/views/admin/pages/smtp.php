<?php if (!defined('ABSPATH')) exit; ?>
<?php whp_get_shared('header'); ?>

<?php
$_fm_subtab = isset($_GET['subtab']) ? sanitize_key($_GET['subtab']) : 'form-manager';
$_fm_tabs = [
    'form-manager' => [
        'label' => __('Quản lý Form', 'whp'),
        'color' => '#7c3aed',
        'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
    ],
    'smtp' => [
        'label' => __('Cấu hình SMTP', 'whp'),
        'color' => '#2563eb',
        'icon'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
    ],
    'spam-filter' => [
        'label' => __('Chống Spam', 'whp'),
        'color' => '#dc2626',
        'icon'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    ],
    'captcha' => [
        'label' => 'CAPTCHA',
        'color' => '#0891b2',
        'icon'  => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    ],
    'email-log' => [
        'label' => __('Nhật ký Email', 'whp'),
        'color' => '#16a34a',
        'icon'  => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="8" y1="22" x2="16" y2="22"/>',
    ],
];
?>
<style>
.wph-smtp-sub-tabs { display:flex; gap:4px; background:#fff; padding:6px; border-radius:12px; border:1px solid #e2e8f0; margin-bottom:22px; box-shadow:0 1px 4px rgba(0,0,0,.04); flex-wrap:wrap; }
.wph-smtp-sub-tab  { padding:8px 14px; text-decoration:none; color:#64748b; font-weight:500; font-size:13px; border-radius:8px; transition:all .2s; display:inline-flex; align-items:center; gap:6px; white-space:nowrap; flex:1; justify-content:center; }
.wph-smtp-sub-tab:hover { background:#f8fafc; color:#0f172a; }
.wph-smtp-sub-tab.active { font-weight:700; }
.wph-smtp-sub-tab.active-form-manager { background:#f5f3ff; color:#7c3aed; }
.wph-smtp-sub-tab.active-smtp         { background:#eff6ff; color:#2563eb; }
.wph-smtp-sub-tab.active-spam-filter  { background:#fef2f2; color:#dc2626; }
.wph-smtp-sub-tab.active-captcha      { background:#ecfeff; color:#0891b2; }
.wph-smtp-sub-tab.active-email-log    { background:#f0fdf4; color:#16a34a; }
</style>

<div class="wph-smtp-sub-tabs">
<?php foreach ($_fm_tabs as $key => $tab):
    $is_active = $_fm_subtab === $key;
    $cls = $is_active ? "active active-{$key}" : '';
    $icon_color = $is_active ? $tab['color'] : '#94a3b8';
?>
    <a href="<?php echo admin_url("admin.php?page=mb-wphelper-smtp&subtab={$key}"); ?>"
       class="wph-smtp-sub-tab <?php echo $cls; ?>">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
             stroke="<?php echo esc_attr($icon_color); ?>"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <?php echo $tab['icon']; ?>
        </svg>
        <?php echo esc_html($tab['label']); ?>
    </a>
<?php endforeach; ?>
</div>

<?php if ($_fm_subtab === 'form-manager'): ?>
<?php wph_form_manager_page_layout(); ?>
<?php elseif ($_fm_subtab === 'spam-filter'): ?>
<?php wph_spam_filter_page_layout(); ?>
<?php elseif ($_fm_subtab === 'captcha'): ?>
<?php wph_captcha_page_layout(); ?>
<?php elseif ($_fm_subtab === 'email-log'): ?>
<?php wph_email_log_page_layout(); ?>
<?php else: // smtp tab ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php esc_html_e('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<?php
// Default SMTP Auth to '1' (Bật) if not defined
$whp_smtp_auth = isset($whp_smtp_auth) && $whp_smtp_auth !== '' ? $whp_smtp_auth : '1';

// Clean default "0" or "." values from text fields for clean display
$whp_smtp_host = ($whp_smtp_host === '0' || $whp_smtp_host === '.') ? '' : $whp_smtp_host;
$whp_smtp_port = ($whp_smtp_port === '0' || $whp_smtp_port === '.') ? '587' : $whp_smtp_port;
$whp_smtp_user = ($whp_smtp_user === '0' || $whp_smtp_user === '.') ? '' : $whp_smtp_user;
$whp_smtp_password = ($whp_smtp_password === '0' || $whp_smtp_password === '.') ? '' : $whp_smtp_password;
$whp_smtp_from_name = ($whp_smtp_from_name === '0' || $whp_smtp_from_name === '.') ? '' : $whp_smtp_from_name;
$whp_smtp_email = ($whp_smtp_email === '0' || $whp_smtp_email === '.') ? '' : $whp_smtp_email;
$whp_smtp_email_receive = ($whp_smtp_email_receive === '0' || $whp_smtp_email_receive === '.') ? '' : $whp_smtp_email_receive;
?>

<style>
/* === SMTP Modern Layout === */
.mb-wph-smtp-wrap {
    font-family: inherit;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 0 40px;
    box-sizing: border-box;
}

/* 2-Column layout */
.mb-wph-smtp-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 22px;
    align-items: start;
}
@media (max-width: 991px) {
    .mb-wph-smtp-layout {
        grid-template-columns: 1fr;
    }
}

/* Header card style — matches Kênh liên hệ pattern */
.mb-wph-smtp-header {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #f0f4ff 45%, #e8f0fd 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(56,88,233,0.1), 0 0 0 1px #e0e7ff;
    margin-bottom: 20px;
    overflow: hidden;
    min-height: 168px;
    display: flex;
    align-items: stretch;
}
.mb-wph-smtp-header-left {
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
.mb-wph-smtp-header-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mb-wph-smtp-header-icon-box {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3858e9, #6b8af5);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(56,88,233,0.3);
}
.mb-wph-smtp-header-right {
    position: absolute;
    inset: 0 0 0 38%;
    overflow: hidden;
    pointer-events: none;
}
.mb-wph-smtp-header-text h1 {
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 4px 0;
}
.mb-wph-smtp-header-text p {
    font-size: 13.5px;
    color: #475569;
    margin: 0 0 6px 0;
    line-height: 1.5;
}
.mb-wph-smtp-header-link {
    font-size: 13px;
    color: #3858e9;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.mb-wph-smtp-header-link:hover {
    text-decoration: underline;
}

/* Top right Test button in header */
.mb-wph-smtp-btn-scroll-test {
    background: #fff;
    border: 1px solid #cbd5e1;
    color: #334155;
    border-radius: 8px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    white-space: nowrap;
}
.mb-wph-smtp-btn-scroll-test:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

/* Card */
.mb-wph-smtp-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px -2px rgba(15,23,42,.03), 0 2px 6px -1px rgba(15,23,42,.01);
}
.mb-wph-smtp-card h3 {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 14px;
}

/* Toggle Switch card */
.mb-wph-smtp-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}
.mb-wph-smtp-toggle-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mb-wph-smtp-toggle-icon {
    width: 38px;
    height: 38px;
    background: #eff2fe;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3858e9;
    flex-shrink: 0;
}
.mb-wph-smtp-toggle-info strong {
    display: block;
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 3px;
}
.mb-wph-smtp-toggle-info span {
    font-size: 13px;
    color: #64748b;
}

.mb-wph-smtp-toggle-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.mb-wph-smtp-toggle-status {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    transition: color 0.2s;
}
.mb-wph-smtp-toggle-status.active {
    color: #22c55e;
}
.mb-wph-smtp-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
}
.mb-wph-smtp-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.mb-wph-smtp-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: #cbd5e1;
    border-radius: 28px;
    transition: .3s ease;
}
.mb-wph-smtp-slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 4px;
    bottom: 4px;
    background: #fff;
    border-radius: 50%;
    transition: .3s ease;
    box-shadow: 0 1px 4px rgba(15,23,42,.15);
}
.mb-wph-smtp-switch input:checked + .mb-wph-smtp-slider {
    background: #22c55e;
}
.mb-wph-smtp-switch input:checked + .mb-wph-smtp-slider:before {
    transform: translateX(24px);
}

/* Server info card inputs */
.mb-wph-smtp-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 20px;
}
@media (max-width: 600px) {
    .mb-wph-smtp-grid-2 {
        grid-template-columns: 1fr;
    }
}

.mb-wph-smtp-field {
    display: flex;
    flex-direction: column;
}
.mb-wph-smtp-field > label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
}
.mb-wph-smtp-input-wrap {
    position: relative;
    display: block;
    width: 100%;
}
.mb-wph-smtp-input {
    width: 100% !important;
    height: 42px !important;
    padding: 0 14px !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 8px !important;
    font-size: 13.5px !important;
    color: #0f172a !important;
    background: #fff !important;
    box-sizing: border-box !important;
    transition: border-color .2s, box-shadow .2s, background .2s !important;
    font-family: inherit !important;
    outline: none !important;
    box-shadow: none !important;
    line-height: 42px !important;
}
.mb-wph-smtp-input:focus {
    border-color: #3858e9 !important;
    box-shadow: 0 0 0 3px rgba(56,88,233,.1) !important;
}
.mb-wph-smtp-input[readonly] {
    background: #f1f5f9 !important;
    color: #64748b !important;
    cursor: not-allowed;
    border-color: #e2e8f0 !important;
}
.mb-wph-smtp-hint {
    font-size: 12px;
    color: #64748b;
    margin-top: 6px;
    line-height: 1.4;
}

/* Password eye icon */
.mb-wph-smtp-password-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.mb-wph-smtp-password-wrap input {
    padding-right: 40px !important;
    line-height: 42px !important;
}
.mb-wph-smtp-password-eye {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #64748b;
    font-size: 16px;
    width: 16px;
    height: 16px;
    line-height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    opacity: 0.7;
    transition: opacity 0.2s;
}
.mb-wph-smtp-password-eye:hover {
    opacity: 1;
}

/* Select wrapper */
.mb-wph-smtp-select-wrap {
    position: relative;
    display: block;
    width: 100%;
}
.mb-wph-smtp-select-wrap select {
    width: 100% !important;
    height: 42px !important;
    padding: 0 34px 0 14px !important;
    border: 1.5px solid #cbd5e1 !important;
    border-radius: 8px !important;
    font-size: 13.5px !important;
    color: #0f172a !important;
    background: #fff !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    cursor: pointer !important;
    outline: none !important;
    box-shadow: none !important;
    box-sizing: border-box !important;
    transition: border-color .2s, box-shadow .2s !important;
    font-family: inherit !important;
    line-height: 42px !important;
}
.mb-wph-smtp-select-wrap select:focus {
    border-color: #3858e9 !important;
    box-shadow: 0 0 0 3px rgba(56,88,233,.1) !important;
}
.mb-wph-smtp-select-chevron {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #64748b;
    display: flex;
    align-items: center;
}

/* Disabled state fields */
.mb-wph-smtp-fields-disabled {
    opacity: 0.45;
    pointer-events: none;
    user-select: none;
}
.mb-wph-smtp-layout.smtp-content-disabled {
    opacity: 0.4;
    pointer-events: none;
    user-select: none;
    transition: opacity 0.3s;
}

/* Advanced collapsible section */
.mb-wph-smtp-adv-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    transition: background 0.2s, border-color 0.2s;
    user-select: none;
    margin-top: 10px;
}
.mb-wph-smtp-adv-toggle:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.mb-wph-smtp-adv-content {
    display: none;
    margin-top: 14px;
    padding: 18px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
}

/* Save / Footer bar */
.mb-wph-smtp-save-bar {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 20px;
    box-shadow: 0 4px 20px -2px rgba(15,23,42,.03);
}
.mb-wph-smtp-save-note {
    font-size: 12.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mb-wph-smtp-save-btn {
    background: linear-gradient(135deg, #3858e9 0%, #2563eb 100%);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 11px 32px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(56,88,233,.35);
    transition: all .2s;
    letter-spacing: .2px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
}
.mb-wph-smtp-save-btn:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(56,88,233,.4);
}

/* Sidebar design */
.mb-wph-smtp-sidebar {
    position: sticky;
    top: 32px;
}
.mb-wph-smtp-sidebar-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px -2px rgba(15,23,42,.03);
}
.mb-wph-smtp-sidebar-card h4 {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Quick guide steps */
.mb-smtp-guide-steps {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.mb-smtp-guide-step {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.mb-smtp-guide-step-num {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #475569;
    font-size: 12.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}
.mb-smtp-guide-step-body strong {
    display: block;
    font-size: 13px;
    color: #334155;
    margin-bottom: 2px;
}
.mb-smtp-guide-step-body span {
    font-size: 12px;
    color: #64748b;
    line-height: 1.4;
    display: block;
}

/* Preset list items */
.mb-smtp-preset-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.mb-smtp-preset-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.2s;
    user-select: none;
}
.mb-smtp-preset-item:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    transform: translateX(2px);
}
.mb-smtp-preset-left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mb-smtp-preset-logo {
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 5px;
}
.mb-smtp-preset-logo svg {
    width: 14px;
    height: 14px;
}
.mb-smtp-preset-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
}
.mb-smtp-preset-info {
    font-size: 11.5px;
    color: #94a3b8;
    font-family: monospace;
}

.mb-smtp-preset-link {
    font-size: 12.5px;
    color: #3858e9;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 14px;
}
.mb-smtp-preset-link:hover {
    text-decoration: underline;
}
</style>
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
</style>
<div id="whp-toast-wrap"></div>

<form method="post" id="mb-smtp-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-smtp-wrap">

    <!-- Header Card -->
    <div class="mb-wph-smtp-header">
        <!-- Left: icon + title + desc + toggle -->
        <div class="mb-wph-smtp-header-left">
            <div class="mb-wph-smtp-header-title-row">
                <div class="mb-wph-smtp-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="2" fill="#fff" fill-opacity=".9"/><path d="M2 7l10 7 10-7" stroke="#3858e9" stroke-width="2" stroke-linecap="round"/></svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Cấu hình SMTP', 'whp'); ?></h1>
            </div>
            <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e('Cấu hình máy chủ SMTP để website có thể gửi email liên hệ, thông báo và xác thực người dùng.', 'whp'); ?></p>
            <div style="display:inline-flex;align-items:center;gap:10px;padding-left:58px;">
                <label class="mb-wph-smtp-switch">
                    <input type="checkbox" id="wpg_smtp_toggle" name="whp_smtp_active" value="1" <?php echo esc_attr($whp_smtp_active_check); ?>>
                    <span class="mb-wph-smtp-slider"></span>
                </label>
                <span class="mb-wph-smtp-toggle-status <?php echo $whp_smtp_active ? 'active' : ''; ?>" id="mb_smtp_toggle_status_text" style="font-size:13px;font-weight:600;">
                    <?php echo $whp_smtp_active ? esc_html__('Đang bật', 'whp') : esc_html__('Đang tắt', 'whp'); ?>
                </span>
            </div>
        </div>
        <!-- Right: illustration -->
        <div class="mb-wph-smtp-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="smtp_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f0f4ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#edf2ff" stop-opacity="0.6"/>
                        <stop offset="100%" stop-color="#dde8ff" stop-opacity="1"/>
                    </linearGradient>
                    <filter id="smtp_shadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(56,88,233,0.18)"/>
                    </filter>
                    <filter id="smtp_shadowSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="rgba(56,88,233,0.12)"/>
                    </filter>
                </defs>
                <!-- bg wash -->
                <rect width="680" height="168" fill="url(#smtp_hbg)"/>
                <!-- decorative circles -->
                <circle cx="580" cy="20" r="60" fill="#3858e9" fill-opacity=".05"/>
                <circle cx="640" cy="140" r="40" fill="#6366f1" fill-opacity=".06"/>
                <circle cx="310" cy="84" r="90" fill="#818cf8" fill-opacity=".04"/>
                <!-- Server rack (right) -->
                <g filter="url(#smtp_shadow)">
                    <rect x="580" y="34" width="72" height="100" rx="8" fill="#1e293b"/>
                    <rect x="585" y="42" width="62" height="14" rx="3" fill="#334155"/>
                    <rect x="585" y="60" width="62" height="14" rx="3" fill="#334155"/>
                    <rect x="585" y="78" width="62" height="14" rx="3" fill="#334155"/>
                    <rect x="585" y="96" width="62" height="14" rx="3" fill="#334155"/>
                    <circle cx="639" cy="49" r="3" fill="#22c55e"/>
                    <circle cx="639" cy="67" r="3" fill="#22c55e"/>
                    <circle cx="639" cy="85" r="3" fill="#fbbf24"/>
                    <circle cx="639" cy="103" r="3" fill="#22c55e"/>
                    <rect x="588" y="45" width="32" height="3" rx="1.5" fill="#475569"/>
                    <rect x="588" y="63" width="28" height="3" rx="1.5" fill="#475569"/>
                    <rect x="588" y="81" width="34" height="3" rx="1.5" fill="#475569"/>
                    <rect x="588" y="99" width="26" height="3" rx="1.5" fill="#475569"/>
                </g>
                <!-- Main envelope (center) -->
                <g filter="url(#smtp_shadow)">
                    <rect x="370" y="44" width="90" height="66" rx="6" fill="#fff"/>
                    <path d="M370 52l45 30 45-30" stroke="#3858e9" stroke-width="2" stroke-linecap="round"/>
                    <line x1="370" y1="110" x2="415" y2="78" stroke="#c7d2fe" stroke-width="1.2"/>
                    <line x1="460" y1="110" x2="415" y2="78" stroke="#c7d2fe" stroke-width="1.2"/>
                </g>
                <!-- Small flying envelope 1 -->
                <g filter="url(#smtp_shadowSm)" transform="translate(460,28) rotate(-12)">
                    <rect width="44" height="32" rx="4" fill="#eff6ff"/>
                    <path d="M0 6l22 14 22-14" stroke="#6366f1" stroke-width="1.5" stroke-linecap="round"/>
                </g>
                <!-- Small flying envelope 2 -->
                <g filter="url(#smtp_shadowSm)" transform="translate(310,100) rotate(8)">
                    <rect width="36" height="26" rx="3" fill="#f0fdf4"/>
                    <path d="M0 5l18 11 18-11" stroke="#22c55e" stroke-width="1.5" stroke-linecap="round"/>
                </g>
                <!-- @ symbol -->
                <text x="510" y="130" font-size="38" font-weight="700" fill="#3858e9" fill-opacity=".12" font-family="monospace">@</text>
                <text x="330" y="55" font-size="26" font-weight="700" fill="#6366f1" fill-opacity=".15" font-family="monospace">@</text>
                <!-- Dotted connection lines -->
                <line x1="460" y1="77" x2="580" y2="77" stroke="#c7d2fe" stroke-width="1.5" stroke-dasharray="4 4"/>
                <circle cx="460" cy="77" r="3.5" fill="#6366f1" fill-opacity=".5"/>
                <circle cx="580" cy="77" r="3.5" fill="#3858e9" fill-opacity=".5"/>
                <!-- Lock icon (security) -->
                <g transform="translate(498,58)">
                    <rect x="0" y="9" width="22" height="16" rx="3" fill="#dbeafe"/>
                    <rect x="4" y="9" width="14" height="9" rx="2" fill="#bfdbfe"/>
                    <path d="M5 9V6a6 6 0 0 1 12 0v3" stroke="#3858e9" stroke-width="2" fill="none" stroke-linecap="round"/>
                    <circle cx="11" cy="17" r="2" fill="#3858e9"/>
                </g>
                <!-- Floating dots -->
                <circle cx="350" cy="30" r="4" fill="#3858e9" fill-opacity=".2"/>
                <circle cx="365" cy="130" r="3" fill="#6366f1" fill-opacity=".2"/>
                <circle cx="548" cy="40" r="5" fill="#818cf8" fill-opacity=".2"/>
                <circle cx="430" cy="145" r="3.5" fill="#3858e9" fill-opacity=".15"/>
            </svg>
        </div>
    </div>

    <!-- 2-Column layout -->
    <div class="mb-wph-smtp-layout<?php echo !$whp_smtp_active ? ' smtp-content-disabled' : ''; ?>">

        <!-- Left col: settings and test cards -->
        <div class="mb-wph-smtp-main">

            <!-- Card 2: Server configuration -->
            <div id="mb-smtp-fields" class="mb-wph-smtp-card">
                <h3>
                    <span class="dashicons dashicons-admin-settings" style="color:#f97316;font-size:17px;width:17px;height:17px;"></span>
                    <?php esc_html_e('Thông tin máy chủ', 'whp'); ?>
                </h3>

                <div class="mb-wph-smtp-grid-2">

                    <!-- Field 1: Máy chủ SMTP -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Máy chủ SMTP', 'whp'); ?></label>
                        <div class="mb-wph-smtp-input-wrap">
                            <input type="text" class="mb-wph-smtp-input" name="whp_smtp_host"
                                placeholder="smtp.gmail.com"
                                value="<?php echo esc_attr($whp_smtp_host ?? ''); ?>">
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Địa chỉ máy chủ SMTP của nhà cung cấp email.', 'whp'); ?></p>
                    </div>

                    <!-- Field 2: Xác thực -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Xác thực', 'whp'); ?></label>
                        <div class="mb-wph-smtp-select-wrap">
                            <select id="mb_smtp_auth" name="whp_smtp_auth">
                                <option value="1" <?php selected($whp_smtp_auth, '1'); ?>><?php esc_html_e('Bật', 'whp'); ?></option>
                                <option value="0" <?php selected($whp_smtp_auth, '0'); ?>><?php esc_html_e('Tắt', 'whp'); ?></option>
                            </select>
                            <span class="mb-wph-smtp-select-chevron">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Bật nếu máy chủ yêu cầu xác thực.', 'whp'); ?></p>
                    </div>

                    <!-- Field 3: Bảo mật kết nối -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Bảo mật kết nối', 'whp'); ?></label>
                        <div class="mb-wph-smtp-select-wrap">
                            <?php
                            $conn_val = 'none';
                            if ($whp_smtp_security === 'tls') $conn_val = 'starttls';
                            elseif ($whp_smtp_security === 'ssl') $conn_val = 'ssl';
                            ?>
                            <select id="mb_smtp_conn_select" name="whp_smtp_security_conn">
                                <option value="starttls" <?php selected($conn_val, 'starttls'); ?>>STARTTLS</option>
                                <option value="ssl" <?php selected($conn_val, 'ssl'); ?>>SSL/TLS</option>
                                <option value="none" <?php selected($conn_val, 'none'); ?>><?php esc_html_e('Không bảo mật', 'whp'); ?></option>
                            </select>
                            <span class="mb-wph-smtp-select-chevron">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Chọn giao thức bảo mật kết nối.', 'whp'); ?></p>
                    </div>

                    <!-- Field 4: Mã hóa -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Mã hóa', 'whp'); ?></label>
                        <div class="mb-wph-smtp-select-wrap">
                            <select id="wpg_smtp_security" name="whp_smtp_security">
                                <option value="tls" <?php selected($whp_smtp_security, 'tls'); ?>>TLS</option>
                                <option value="ssl" <?php selected($whp_smtp_security, 'ssl'); ?>>SSL</option>
                                <option value="none" <?php selected($whp_smtp_security, 'none'); ?>><?php esc_html_e('Không', 'whp'); ?></option>
                            </select>
                            <span class="mb-wph-smtp-select-chevron">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Chọn mã hóa phù hợp với máy chủ SMTP.', 'whp'); ?></p>
                    </div>

                    <!-- Field 5: Cổng SMTP -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Cổng SMTP', 'whp'); ?></label>
                        <div class="mb-wph-smtp-input-wrap">
                            <input type="text" class="mb-wph-smtp-input" name="whp_smtp_port" id="wpg_smtp_port"
                                placeholder="587"
                                value="<?php echo esc_attr($whp_smtp_port ?: '587'); ?>">
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Cổng kết nối. Thường là 587 (TLS) hoặc 465 (SSL).', 'whp'); ?></p>
                    </div>

                    <!-- Field 6: Tên người gửi -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Tên người gửi', 'whp'); ?></label>
                        <div class="mb-wph-smtp-input-wrap">
                            <input type="text" class="mb-wph-smtp-input" name="whp_smtp_from_name"
                                placeholder="<?php esc_attr_e('Ví dụ: Công ty ABC hoặc Support Team', 'whp'); ?>"
                                value="<?php echo esc_attr($whp_smtp_from_name ?? ''); ?>">
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Tên hiển thị khi gửi email đi.', 'whp'); ?></p>
                    </div>

                    <!-- Field 7: Tên đăng nhập SMTP -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Tên đăng nhập SMTP', 'whp'); ?></label>
                        <div class="mb-wph-smtp-input-wrap">
                            <input type="text" class="mb-wph-smtp-input" name="whp_smtp_user"
                                placeholder="your.email@gmail.com"
                                value="<?php echo esc_attr($whp_smtp_user ?? ''); ?>">
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Tài khoản email dùng để gửi thư.', 'whp'); ?></p>
                    </div>

                    <!-- Field 8: Mật khẩu SMTP -->
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Mật khẩu SMTP', 'whp'); ?></label>
                        <div class="mb-wph-smtp-password-wrap">
                            <input type="password" class="mb-wph-smtp-input" name="whp_smtp_password" id="wpg_smtp_password"
                                placeholder="<?php esc_attr_e('Mật khẩu ứng dụng (App Password)', 'whp'); ?>"
                                value="<?php echo esc_attr($whp_smtp_password ?? ''); ?>">
                            <span class="dashicons dashicons-visibility mb-wph-smtp-password-eye" id="wpg_toggle_password"></span>
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Mật khẩu ứng dụng (nếu bật xác thực 2 bước).', 'whp'); ?></p>
                    </div>

                </div>

                <!-- Advanced settings section -->
                <div class="mb-wph-smtp-adv-toggle" id="mb_smtp_adv_toggle">
                    <span style="display:inline-flex; align-items:center; gap:6px;">
                        <span class="dashicons dashicons-admin-generic" style="font-size:15px; width:15px; height:15px; line-height:15px;"></span>
                        <?php esc_html_e('Tùy chọn nâng cao', 'whp'); ?>
                    </span>
                    <span class="dashicons dashicons-arrow-down-alt2" id="mb_smtp_adv_arrow" style="transition: transform 0.25s;"></span>
                </div>
                
                <div class="mb-wph-smtp-adv-content" id="mb_smtp_adv_content">
                    <div class="mb-wph-smtp-field">
                        <label><?php esc_html_e('Được gửi từ email', 'whp'); ?></label>
                        <div class="mb-wph-smtp-input-wrap">
                            <input type="text" class="mb-wph-smtp-input" name="whp_smtp_email"
                                placeholder="<?php esc_attr_e('Vd: support@gmail.com', 'whp'); ?>"
                                value="<?php echo esc_attr($whp_smtp_email ?? ''); ?>">
                        </div>
                        <p class="mb-wph-smtp-hint"><?php esc_html_e('Email này sẽ xuất hiện trong trường "From" của email gửi đi.', 'whp'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Card 3: Test configuration SMTP -->
            <div class="mb-wph-smtp-card" id="mb_smtp_test_card" style="background: #f5f8ff; border: 1px solid #dbeafe; box-shadow: 0 4px 14px rgba(56,88,233,0.02);">
                <h3 style="color: #1e3a8a; border-bottom-color: #dbeafe; margin-bottom: 14px; padding-bottom: 10px;">
                    <span class="dashicons dashicons-email-alt" style="color:#2563eb;font-size:17px;width:17px;height:17px;"></span>
                    <?php esc_html_e('Kiểm tra cấu hình SMTP', 'whp'); ?>
                </h3>
                <p style="font-size: 13px; color: #4b5563; margin: 0 0 16px 0;"><?php esc_html_e('Gửi email thử nghiệm để đảm bảo cấu hình hoạt động chính xác.', 'whp'); ?></p>

                <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 250px;">
                        <input type="text" class="mb-wph-smtp-input" id="whp_smtp_email_receive" name="whp_smtp_email_receive"
                            placeholder="<?php esc_attr_e('Nhập email nhận thử nghiệm', 'whp'); ?>"
                            style="background: #fff; border: 1.5px solid #bfdbfe; font-size: 13.5px;"
                            value="<?php echo esc_attr(whp_get_setting('whp_smtp_email_receive') ?: ''); ?>">
                    </div>
                    <button type="button" class="mb-wph-smtp-save-btn" id="test_mail" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #3858e9; color: #fff; border: none; border-radius: 8px; padding: 11px 24px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px rgba(56,88,233,0.25);">
                        <span class="dashicons dashicons-paper-plane" style="font-size: 14px; width: 14px; height: 14px; line-height: 14px;"></span>
                        <?php esc_html_e('Gửi email thử nghiệm', 'whp'); ?>
                    </button>
                </div>
                <div style="margin-top: 10px; font-size: 12.5px; color: #4b5563;" id="mb_smtp_test_dest_note">
                    <?php esc_html_e('Email thử nghiệm sẽ được gửi tới địa chỉ:', 'whp'); ?> <strong id="mb_smtp_test_dest_email"><?php echo esc_html(whp_get_setting('whp_smtp_email_receive') ?: esc_html__('chưa nhập', 'whp')); ?></strong>
                </div>
            </div>

        </div><!-- /left col -->

        <!-- Right col: Sidebar -->
        <div class="mb-wph-smtp-sidebar">

            <!-- Sidebar Card 1: Hướng dẫn nhanh -->
            <div class="mb-wph-smtp-sidebar-card">
                <h4>
                    <span class="dashicons dashicons-lightbulb" style="color: #eab308; font-size:17px; width:17px; height:17px; line-height:17px;"></span>
                    <?php esc_html_e('Hướng dẫn nhanh', 'whp'); ?>
                </h4>
                <div class="mb-smtp-guide-steps">
                    <div class="mb-smtp-guide-step">
                        <div class="mb-smtp-guide-step-num">1</div>
                        <div class="mb-smtp-guide-step-body">
                            <strong><?php esc_html_e('Lấy thông tin SMTP', 'whp'); ?></strong>
                            <span><?php esc_html_e('Đăng nhập vào tài khoản email của bạn (Gmail, Outlook...) và lấy thông tin SMTP.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div class="mb-smtp-guide-step">
                        <div class="mb-smtp-guide-step-num">2</div>
                        <div class="mb-smtp-guide-step-body">
                            <strong><?php esc_html_e('Nhập thông tin', 'whp'); ?></strong>
                            <span><?php esc_html_e('Điền chính xác các thông tin máy chủ, cổng, tài khoản và mật khẩu.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div class="mb-smtp-guide-step">
                        <div class="mb-smtp-guide-step-num">3</div>
                        <div class="mb-smtp-guide-step-body">
                            <strong><?php esc_html_e('Kiểm tra và lưu', 'whp'); ?></strong>
                            <span><?php esc_html_e('Gửi email thử nghiệm để đảm bảo cấu hình hoạt động.', 'whp'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Card 2: Cấu hình phổ biến -->
            <div class="mb-wph-smtp-sidebar-card">
                <h4>
                    <span class="dashicons dashicons-networking" style="color: #3858e9; font-size:17px; width:17px; height:17px; line-height:17px;"></span>
                    <?php esc_html_e('Cấu hình phổ biến', 'whp'); ?>
                </h4>
                <div class="mb-smtp-preset-list">
                    <!-- Preset: Gmail -->
                    <div class="mb-smtp-preset-item" data-provider="gmail" title="<?php esc_attr_e('Nhấp để áp dụng cấu hình nhanh', 'whp'); ?>">
                        <div class="mb-smtp-preset-left">
                            <div class="mb-smtp-preset-logo" style="background: #fee2e2;">
                                <svg viewBox="0 0 24 24" fill="#EA4335"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                            </div>
                            <span class="mb-smtp-preset-name">Gmail</span>
                        </div>
                        <span class="mb-smtp-preset-info">smtp.gmail.com / 587</span>
                    </div>

                    <!-- Preset: Outlook -->
                    <div class="mb-smtp-preset-item" data-provider="outlook" title="<?php esc_attr_e('Nhấp để áp dụng cấu hình nhanh', 'whp'); ?>">
                        <div class="mb-smtp-preset-left">
                            <div class="mb-smtp-preset-logo" style="background: #e0f2fe;">
                                <svg viewBox="0 0 24 24" fill="#0078D4"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14h-4v-4h4v4zm0-6h-4V7h4v4z"/></svg>
                            </div>
                            <span class="mb-smtp-preset-name">Outlook</span>
                        </div>
                        <span class="mb-smtp-preset-info">smtp.office365.com / 587</span>
                    </div>

                    <!-- Preset: Zoho Mail -->
                    <div class="mb-smtp-preset-item" data-provider="zoho" title="<?php esc_attr_e('Nhấp để áp dụng cấu hình nhanh', 'whp'); ?>">
                        <div class="mb-smtp-preset-left">
                            <div class="mb-smtp-preset-logo" style="background: #fef3c7;">
                                <svg viewBox="0 0 24 24" fill="#E21B1B"><circle cx="7" cy="7" r="5" fill="#E21B1B"/><circle cx="17" cy="7" r="5" fill="#1488E0"/><circle cx="7" cy="17" r="5" fill="#F4B000"/><circle cx="17" cy="17" r="5" fill="#109E2B"/></svg>
                            </div>
                            <span class="mb-smtp-preset-name">Zoho Mail</span>
                        </div>
                        <span class="mb-smtp-preset-info">smtp.zoho.com / 587</span>
                    </div>

                    <!-- Preset: Yahoo Mail -->
                    <div class="mb-smtp-preset-item" data-provider="yahoo" title="<?php esc_attr_e('Nhấp để áp dụng cấu hình nhanh', 'whp'); ?>">
                        <div class="mb-smtp-preset-left">
                            <div class="mb-smtp-preset-logo" style="background: #f3e8ff;">
                                <svg viewBox="0 0 24 24" fill="#6001d2"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-1.5 5.5l-4 4.5 4 4.5h-2.5l-2.75-3.25L10.5 18.5H8l4-4.5-4-4.5h2.5l2.75 3.25 2.75-3.25h2.5z"/></svg>
                            </div>
                            <span class="mb-smtp-preset-name">Yahoo Mail</span>
                        </div>
                        <span class="mb-smtp-preset-info">smtp.mail.yahoo.com / 465</span>
                    </div>
                </div>

                <a href="https://wiki.matbao.net/kb/thong-tin-smtp-gmail-cach-cau-hinh-smtp-gmail-free-vao-wordpress/" target="_blank" class="mb-smtp-preset-link">
                    <?php esc_html_e('Xem thêm hướng dẫn', 'whp'); ?> <span class="dashicons dashicons-external" style="font-size:12px; width:12px; height:12px; line-height:12px;"></span>
                </a>
            </div>

        </div><!-- /right col -->

    </div><!-- /layout -->

    <!-- Save Bar -->
    <div id="mb-smtp-save-bar" class="mb-wph-smtp-save-bar">
        <span class="mb-wph-smtp-save-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="vertical-align:middle;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            <?php esc_html_e('Các thay đổi sẽ được áp dụng ngay sau khi lưu.', 'whp'); ?>
        </span>
        <button type="submit" name="submit" class="mb-wph-smtp-save-btn">
            <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
            <?php esc_html_e('Lưu cấu hình', 'whp'); ?>
        </button>
    </div>

</div><!-- /.mb-wph-smtp-wrap -->

<script>
var whpSmtpI18n = {
    enabled:   '<?php echo esc_js( __( 'Đang bật', 'whp' ) ); ?>',
    disabled:  '<?php echo esc_js( __( 'Đang tắt', 'whp' ) ); ?>',
    toggleOn:  '<?php echo esc_js( __( 'Đã bật Cấu hình SMTP', 'whp' ) ); ?>',
    toggleOff: '<?php echo esc_js( __( 'Đã tắt Cấu hình SMTP', 'whp' ) ); ?>',
    saveError:        '<?php echo esc_js( __( 'Lỗi lưu trạng thái', 'whp' ) ); ?>',
    connError:        '<?php echo esc_js( __( 'Lỗi kết nối', 'whp' ) ); ?>',
    notEntered:       '<?php echo esc_js( __( 'chưa nhập', 'whp' ) ); ?>',
    testEmailContent: '<?php echo esc_js( __( 'Đây là email thử nghiệm từ WP Helper Lite. Cấu hình máy chủ SMTP hoạt động hoàn toàn chính xác!', 'whp' ) ); ?>',
    alertEnterEmail:  '<?php echo esc_js( __( 'Vui lòng nhập địa chỉ email nhận thử nghiệm.', 'whp' ) ); ?>',
    sending:          '<?php echo esc_js( __( 'Đang gửi...', 'whp' ) ); ?>',
    testSuccess:      '<?php echo esc_js( __( 'Bạn đã gửi email thử nghiệm thành công!', 'whp' ) ); ?>',
    testFailed:       '<?php echo esc_js( __( 'Gửi email thất bại:', 'whp' ) ); ?>',
    connErrorSend:    '<?php echo esc_js( __( 'Đã xảy ra lỗi kết nối trong quá trình gửi mail. Vui lòng kiểm tra lại.', 'whp' ) ); ?>',
    sendTestEmail:    '<?php echo esc_js( __( 'Gửi email thử nghiệm', 'whp' ) ); ?>'
};
function whpToast(msg, type) {
    var wrap = document.getElementById('whp-toast-wrap');
    if (!wrap) return;
    type = type || 'success';
    var icons = {success:'✓', error:'✗'};
    var t = document.createElement('div');
    t.className = 'whp-toast wt-' + type;
    t.innerHTML = '<div class="whp-toast-icon">' + (icons[type]||'✓') + '</div>'
                + '<span class="whp-toast-msg">' + msg + '</span>'
                + '<button class="whp-toast-close" onclick="this.closest(\'.whp-toast\').remove()">×</button>';
    wrap.appendChild(t);
    setTimeout(function(){ t.classList.add('wt-out'); setTimeout(function(){ t.remove(); }, 280); }, 3800);
}
jQuery(document).ready(function($) {
    'use strict';

    // Toggle kích hoạt ẩn/hiện fields
    var $smtpToggle = $('#wpg_smtp_toggle');
    var $smtpFields = $('#mb-smtp-fields');
    var $statusText = $('#mb_smtp_toggle_status_text');

    function updateSmtpFieldsVisibility(doSave) {
        var $saveBar = $('#mb-smtp-save-bar');
        var isOn = $smtpToggle.is(':checked');
        $('.mb-wph-smtp-layout').toggleClass('smtp-content-disabled', !isOn);
        $statusText.text(isOn ? whpSmtpI18n.enabled : whpSmtpI18n.disabled).toggleClass('active', isOn);
        $saveBar.css('display', isOn ? '' : 'none');
        if (doSave) {
            var _isOn = $smtpToggle.is(':checked');
            var _nonce = '<?php echo esc_js( wp_create_nonce('whp_smtp_toggle') ); ?>';
            var _fd = new FormData();
            _fd.append('action', 'whp_smtp_toggle_enable');
            _fd.append('nonce', _nonce);
            _fd.append('active', _isOn ? '1' : '0');
            fetch(ajaxurl, { method: 'POST', body: _fd })
                .then(function(r){ return r.json(); })
                .then(function(r){
                    if (r.success) {
                        whpToast(_isOn ? whpSmtpI18n.toggleOn : whpSmtpI18n.toggleOff, 'success');
                    } else {
                        whpToast(whpSmtpI18n.saveError, 'error');
                    }
                })
                .catch(function(){ whpToast(whpSmtpI18n.connError, 'error'); });
        }
    }
    $smtpToggle.on('change', function() { updateSmtpFieldsVisibility(true); });
    updateSmtpFieldsVisibility(false);

    // Đồng bộ dropdown Bảo mật kết nối và Mã hóa + Tự điền Cổng SMTP
    var $securitySelect = $('#wpg_smtp_security'); // whp_smtp_security (database)
    var $connSelect     = $('#mb_smtp_conn_select');     // whp_smtp_security_conn
    var $portInput      = $('#wpg_smtp_port');

    // Khi thay đổi "Bảo mật kết nối"
    $connSelect.on('change', function() {
        var conn = $(this).val();
        if (conn === 'starttls') {
            $securitySelect.val('tls');
            $portInput.val('587');
        } else if (conn === 'ssl') {
            $securitySelect.val('ssl');
            $portInput.val('465');
        } else if (conn === 'none') {
            $securitySelect.val('none');
            $portInput.val('25');
        }
    });

    // Khi thay đổi "Mã hóa"
    $securitySelect.on('change', function() {
        var enc = $(this).val();
        if (enc === 'tls') {
            $connSelect.val('starttls');
            $portInput.val('587');
        } else if (enc === 'ssl') {
            $connSelect.val('ssl');
            $portInput.val('465');
        } else {
            $connSelect.val('none');
            $portInput.val('25');
        }
    });

    // Thay đổi trạng thái Xác thực
    var $authSelect = $('#mb_smtp_auth');
    var $userInput  = $('input[name="whp_smtp_user"]');
    var $passInput  = $('input[name="whp_smtp_password"]');

    function updateAuthFields() {
        var auth = $authSelect.val();
        if (auth === '0') {
            $userInput.prop('disabled', true).addClass('mb-wph-smtp-fields-disabled').css('background', '#f1f5f9');
            $passInput.prop('disabled', true).addClass('mb-wph-smtp-fields-disabled').css('background', '#f1f5f9');
        } else {
            $userInput.prop('disabled', false).removeClass('mb-wph-smtp-fields-disabled').css('background', '');
            $passInput.prop('disabled', false).removeClass('mb-wph-smtp-fields-disabled').css('background', '');
        }
    }
    $authSelect.on('change', updateAuthFields);
    updateAuthFields();

    // Hiện/ẩn mật khẩu
    $('#wpg_toggle_password').on('click', function() {
        var $pass = $('#wpg_smtp_password');
        if ($pass.attr('type') === 'password') {
            $pass.attr('type', 'text');
            $(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            $pass.attr('type', 'password');
            $(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

    // Toggle Tùy chọn nâng cao
    var $advToggle = $('#mb_smtp_adv_toggle');
    var $advContent = $('#mb_smtp_adv_content');
    var $advArrow = $('#mb_smtp_adv_arrow');

    $advToggle.on('click', function() {
        $advContent.slideToggle(200);
        var isCollapsed = $advArrow.css('transform') === 'none' || $advArrow.css('transform') === 'matrix(1, 0, 0, 1, 0, 0)';
        if (isCollapsed) {
            $advArrow.css('transform', 'rotate(180deg)');
        } else {
            $advArrow.css('transform', 'rotate(0deg)');
        }
    });

    // Cuộn mượt đến card kiểm tra thử nghiệm
    $('#mb_smtp_btn_test_scroll').on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $('#mb_smtp_test_card').offset().top - 50
        }, 500);
        $('#whp_smtp_email_receive').focus();
    });

    // Hiển thị dynamic email nhận thử nghiệm bên dưới card
    $('#whp_smtp_email_receive').on('input', function() {
        var val = $(this).val().trim();
        $('#mb_smtp_test_dest_email').text(val ? val : whpSmtpI18n.notEntered);
    });

    // Điền nhanh từ danh sách nhà cung cấp phổ biến ở sidebar
    var presets = {
        gmail: { host: 'smtp.gmail.com', security: 'tls', port: '587' },
        outlook: { host: 'smtp.office365.com', security: 'tls', port: '587' },
        zoho: { host: 'smtp.zoho.com', security: 'tls', port: '587' },
        yahoo: { host: 'smtp.mail.yahoo.com', security: 'ssl', port: '465' }
    };

    $('.mb-smtp-preset-item').on('click', function() {
        var provider = $(this).data('provider');
        var p = presets[provider];
        if (p) {
            $('input[name="whp_smtp_host"]').val(p.host);
            $securitySelect.val(p.security).trigger('change');
            $portInput.val(p.port);
            $authSelect.val('1').trigger('change');

            // Flash effect to draw attention
            var $hostInput = $('input[name="whp_smtp_host"]');
            $hostInput.css('border-color', '#3858e9').css('box-shadow', '0 0 0 3px rgba(56,88,233,.2)');
            setTimeout(function() {
                $hostInput.css('border-color', '').css('box-shadow', '');
            }, 800);
        }
    });

    // AJAX gửi email thử nghiệm
    $('#test_mail').on('click', function(e) {
        e.preventDefault();
        var wp_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
        var email       = $('#whp_smtp_email_receive').val().trim();
        var content     = whpSmtpI18n.testEmailContent;
        var nonce       = '<?php echo wp_create_nonce('whp_smtp_send_mail_test_nonce'); ?>';
        var $button     = $(this);

        if (!email) {
            alert(whpSmtpI18n.alertEnterEmail);
            $('#whp_smtp_email_receive').focus();
            return;
        }

        $button.prop('disabled', true).html('<span class="dashicons dashicons-update" style="font-size:14px; width:14px; height:14px; line-height:14px; animation: spin 1s infinite linear;"></span> ' + whpSmtpI18n.sending);

        $.ajax({
            type: 'post',
            url: wp_ajax_url,
            data: {
                'action':  'whp_smtp_send_mail_test',
                'email':   email,
                'content': content,
                'nonce':   nonce
            },
            dataType: 'json',
            success: function(res) {
                if (res['status'] == 200) {
                    alert(whpSmtpI18n.testSuccess);
                } else {
                    alert(whpSmtpI18n.testFailed + ' ' + res['message']);
                }
            },
            error: function() {
                alert(whpSmtpI18n.connErrorSend);
            },
            complete: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-paper-plane" style="font-size:14px; width:14px; height:14px; line-height:14px;"></span> ' + whpSmtpI18n.sendTestEmail);
            }
        });
    });
});

    // ── Auto scroll + highlight SMTP test card when coming from email-log ──────
    (function() {
        if (window.location.hash !== '#mb_smtp_test_card') return;
        var card = document.getElementById('mb_smtp_test_card');
        if (!card) return;
        setTimeout(function() {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            var origBorder = card.style.border;
            var origBg     = card.style.background;
            var origShadow = card.style.boxShadow;
            var on = false;
            card.style.transition = 'border .25s, box-shadow .25s, background .25s';
            var count = 0, max = 6;
            var iv = setInterval(function() {
                on = !on;
                card.style.border     = on ? '2px solid #3858e9' : '1px solid #dbeafe';
                card.style.boxShadow  = on ? '0 0 0 4px rgba(56,88,233,.18)' : '0 4px 14px rgba(56,88,233,.02)';
                card.style.background = on ? '#eef2ff' : '#f5f8ff';
                if (++count >= max * 2) {
                    clearInterval(iv);
                    card.style.border     = origBorder;
                    card.style.boxShadow  = origShadow;
                    card.style.background = origBg;
                }
            }, 350);
        }, 400);
    })();
</script>

<style>
/* CSS spinner animation for test button */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

</form>

<?php endif; // end smtp tab ?>

<?php whp_get_shared('footer'); ?>
