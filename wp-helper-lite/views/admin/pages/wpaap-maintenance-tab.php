<?php
if (! defined('ABSPATH')) {
    exit;
}

function wpaap_maintenance_tab_layout()
{
    $active       = whp_get_setting('whp_maintenance_active') ?? '';
    $title        = whp_get_setting('whp_maintenance_title') ?? '';
    $heading      = whp_get_setting('whp_maintenance_heading') ?? '';
    $heading_sub  = whp_get_setting('whp_maintenance_heading_sub') ?? '';
    $desc         = whp_get_setting('whp_maintenance_desc') ?? '';
    $logo         = whp_get_setting('whp_maintenance_logo') ?? '';
    $countdown      = whp_get_setting('whp_maintenance_countdown') ?? '';
    $social_phone    = whp_get_option('whp_maintenance_phone')    ?: '';
    $social_email    = whp_get_option('whp_maintenance_email')    ?: '';
    $social_facebook = whp_get_option('whp_maintenance_facebook') ?: '';
    $social_youtube  = whp_get_option('whp_maintenance_youtube')  ?: '';
    $social_zalo     = whp_get_option('whp_maintenance_zalo')     ?: '';
    $social_tiktok   = whp_get_option('whp_maintenance_tiktok')   ?: '';
    $tpl            = whp_get_option('whp_maintenance_template') ?: 'dark';
    $nonce        = wp_create_nonce('wpaap_maintenance_nonce');
    $maint_ai_ok  = false;
    if ( function_exists( 'wpaap_is_provider_connected' ) ) {
        foreach ( [ 'google', 'anthropic', 'openai' ] as $_p ) {
            if ( wpaap_is_provider_connected( $_p ) ) { $maint_ai_ok = true; break; }
        }
    } else {
        $maint_ai_ok = get_option( 'wpaap_core_connected', 'no' ) === 'yes';
    }
    // Preview URL dùng HMAC-SHA256 — không cần cookie/session/database
    $pt_ts   = time();
    $pt_hmac = hash_hmac('sha256', 'wpaap_preview:' . $pt_ts, wp_salt('auth'));
    $preview_url = home_url('/') . '?wpaap_maintenance_preview=1&pt_ts=' . $pt_ts . '&pt_h=' . $pt_hmac;
    ?>
    <style>
    .mb-wph-maint-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px;
        align-items: start;
    }
    .mb-wph-maint-layout > * { min-width: 0; }
    @media (max-width: 1100px) {
        .mb-wph-maint-layout { grid-template-columns: 1fr; }
    }
    .mb-wph-maint-header {
        position: relative;
        background: linear-gradient(100deg, #ffffff 0%, #fffbeb 45%, #fef3c7 100%);
        border-radius: 20px;
        box-shadow: 0 4px 24px rgba(217,119,6,0.1), 0 0 0 1px #fde68a;
        margin-bottom: 25px;
        overflow: hidden;
        min-height: 168px;
        display: flex;
        align-items: stretch;
    }
    .mb-wph-maint-header-left {
        position: relative; z-index: 2;
        padding: 32px 36px;
        display: flex; flex-direction: column; justify-content: center; gap: 14px;
        max-width: 500px; flex-shrink: 0;
    }
    .mb-wph-maint-header-title-row { display: flex; align-items: center; gap: 14px; }
    .mb-wph-maint-header-icon-box {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #d97706, #fbbf24);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; box-shadow: 0 4px 12px rgba(217,119,6,0.3);
    }
    .mb-wph-maint-header-right {
        position: absolute; inset: 0 0 0 38%;
        overflow: hidden; pointer-events: none;
    }
    .mb-wph-maint-toggle-wrap { display: flex; align-items: center; gap: 12px; }
    .mb-wph-maint-toggle-label { font-size: 13.5px; color: #475569; font-weight: 700; }
    .mb-toggle-switch { position: relative; display: inline-block; width: 52px; height: 28px; }
    .mb-toggle-switch input { opacity: 0; width: 0; height: 0; }
    .mb-toggle-slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #cbd5e1; border-radius: 28px; transition: .3s ease;
    }
    .mb-toggle-slider:before {
        position: absolute; content: "";
        height: 20px; width: 20px; left: 4px; bottom: 4px;
        background: #fff; border-radius: 50%;
        transition: .3s ease; box-shadow: 0 1px 4px rgba(15, 23, 42, 0.15);
    }
    .mb-toggle-switch input:checked + .mb-toggle-slider { background: #22c55e; }
    .mb-toggle-switch input:checked + .mb-toggle-slider:before { transform: translateX(24px); }
    .mb-wph-maint-toggle-label.active { color: #22c55e; }
 
    .mb-wph-maint-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03), 0 2px 6px -1px rgba(15, 23, 42, 0.01);
    }
    .mb-wph-maint-card h3 {
        font-size: 14.5px; font-weight: 700;
        color: #0f172a; margin: 0 0 20px;
        display: flex; align-items: center; gap: 10px;
        border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;
    }
    .mb-wph-maint-field { margin-bottom: 18px; }
    .mb-wph-maint-field label {
        display: block; font-size: 13px; font-weight: 600;
        color: #334155; margin-bottom: 6px;
    }
    .mb-wph-maint-field input[type="text"],
    .mb-wph-maint-field input[type="datetime-local"],
    .mb-wph-maint-field textarea {
        width: 100%; border: 1.5px solid #d1d5db;
        border-radius: 8px; padding: 10px 14px;
        font-size: 13.5px; color: #0f172a;
        background-color: #f8fafc;
        transition: all 0.2s ease-in-out; box-sizing: border-box;
        font-family: inherit;
    }
    .mb-wph-maint-field input[type="datetime-local"] {
        height: 40px;
    }
    .mb-wph-maint-field input[type="text"]:focus,
    .mb-wph-maint-field input[type="datetime-local"]:focus,
    .mb-wph-maint-field textarea:focus {
        border-color: #3858e9; outline: none;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(56,88,233,.12);
    }
    .mb-wph-maint-field textarea { min-height: 80px; resize: vertical; }
    
    #wpaap_maint_logo_upload_btn {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #334155;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        height: 40px;
        padding: 0 16px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    #wpaap_maint_logo_upload_btn:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
        color: #0f172a;
    }

    .mb-wph-maint-ai-row {
        display: flex; gap: 12px; align-items: center;
        background: linear-gradient(135deg, #f5f7ff 0%, #eef2ff 100%);
        border: 1.5px dashed #c7d2fe;
        border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;
    }
    .mb-wph-maint-ai-row p { margin: 0; font-size: 13px; color: #3730a3; font-weight: 500; flex: 1; }
    #wpaap_maint_ai_btn {
        flex-shrink: 0;
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: #fff;
        border: none; border-radius: 8px;
        padding: 9px 16px; font-size: 13px; font-weight: 600;
        cursor: pointer; transition: all 0.2s ease;
    }
    #wpaap_maint_ai_btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
    }
    #wpaap_maint_ai_btn:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }
    
    .mb-wph-maint-save-bar {
        display: flex; align-items: center; justify-content: space-between;
        background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
        padding: 14px 18px; margin-top: 20px;
        box-shadow: 0 2px 6px rgba(15,23,42,.04);
    }
    .mb-wph-maint-save-bar-note {
        display: flex; align-items: center; gap: 7px;
        font-size: 12.5px; color: #64748b; line-height: 1.5;
    }
    #wpaap_maint_save_btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: #3858e9; color: #fff;
        border: none; border-radius: 8px;
        padding: 10px 24px; font-size: 13.5px; font-weight: 700;
        cursor: pointer; transition: all 0.2s ease; white-space: nowrap; flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(56,88,233,.3);
    }
    #wpaap_maint_save_btn:hover { background: #2d46cc; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(56,88,233,.38); }
    #wpaap_maint_save_btn:disabled { background: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }

    #wpaap_maint_notice, #wpaap_maint_ai_notice {
        padding: 10px 16px; border-radius: 8px;
        font-size: 13px; font-weight: 600; margin-top: 10px; display: none;
    }
    #wpaap_maint_notice.success, #wpaap_maint_ai_notice.success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    #wpaap_maint_notice.error, #wpaap_maint_ai_notice.error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    #wpaap_maint_notice.warning, #wpaap_maint_ai_notice.warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

    /* Preview panel */
    .mb-wph-maint-preview-panel { position: sticky; top: 32px; }
    .mb-wph-maint-preview-card {
        border: 1px solid #fde68a;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(217,119,6,0.15);
    }
    .mb-wph-maint-preview-header {
        background: linear-gradient(100deg, #fffbeb 0%, #fef3c7 60%, #fde68a 100%);
        padding: 16px 20px 14px;
    }
    .mb-wph-maint-preview-header h3 { margin: 0 0 3px; font-size: 15px; font-weight: 700; color: #0f172a; }
    .mb-wph-maint-preview-header p { margin: 0; font-size: 13px; color: #78350f; }
    .mb-wph-maint-preview-window {
        overflow: hidden;
        width: 100%;
    }
    #wpaap_maint_preview_frame { border: none; display: block; position: absolute; top: 0; left: 0; background: #e5e7eb; transform-origin: top left; pointer-events: none; opacity: 0; transition: opacity 0.3s ease; }
    #wpaap_maint_preview_frame.loaded { opacity: 1; }
    .mb-wph-maint-preview-frame-wrap { overflow: hidden; position: relative; width: 100%; padding-top: 66%; }
    .mb-wph-maint-preview-skeleton {
        position: absolute; inset: 0;
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: wpaap-skeleton-shimmer 1.4s infinite;
        display: flex; align-items: center; justify-content: center;
        flex-direction: column; gap: 10px;
        transition: opacity 0.3s ease;
    }
    .mb-wph-maint-preview-skeleton.hidden { opacity: 0; pointer-events: none; }
    .mb-wph-maint-preview-skeleton svg { opacity: 0.35; }
    .mb-wph-maint-preview-skeleton span { font-size: 12px; color: #94a3b8; font-weight: 500; }
    @keyframes wpaap-skeleton-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

    /* Browser chrome bar (desktop mode) */
    .mb-wph-browser-bar {
        height: 38px; background: #f1f5f9; border-bottom: 1px solid #dde1e7;
        display: flex; align-items: center; gap: 10px; padding: 0 14px; flex-shrink: 0;
    }
    .mb-wph-browser-dots { display: flex; gap: 5px; align-items: center; flex-shrink: 0; }
    .mb-wph-browser-dots span { display: inline-block; width: 10px; height: 10px; border-radius: 50%; }
    .mb-wph-browser-dots span:nth-child(1) { background: #ff5f57; }
    .mb-wph-browser-dots span:nth-child(2) { background: #ffbd2e; }
    .mb-wph-browser-dots span:nth-child(3) { background: #28c940; }
    .mb-wph-browser-url {
        flex: 1; min-width: 0; display: flex; align-items: center; gap: 5px;
        background: #fff; border: 1px solid #dde1e7; border-radius: 6px; padding: 4px 9px;
        font-size: 11.5px; color: #64748b;
    }
    .mb-wph-browser-url svg { flex-shrink: 0; }
    .mb-wph-browser-url span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .mb-wph-maint-test-bar {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 4px; margin-top: 10px;
    }
    .mb-wph-maint-test-bar-left { display: flex; align-items: center; gap: 8px; }
    .mb-wph-maint-test-label { font-size: 13px; font-weight: 600; color: #475569; }
    .mb-wph-maint-device-btn {
        display: inline-flex; align-items: center; justify-content: center;
        width: 36px; height: 36px; border-radius: 8px;
        border: 1.5px solid #e2e8f0; background: #fff;
        cursor: pointer; color: #64748b; transition: all 0.15s;
    }
    .mb-wph-maint-device-btn.active { border-color: #d97706; background: #fffbeb; color: #d97706; }
    .mb-wph-maint-device-btn:hover:not(.active) { border-color: #cbd5e1; background: #f8fafc; }
    #mb_maint_device_label { font-size: 12px; font-weight: 600; color: #d97706; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 3px 8px; }
    .mb-wph-maint-reload-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 14px; border-radius: 8px;
        border: 1.5px solid #e2e8f0; background: #fff;
        font-size: 13px; font-weight: 600; color: #475569;
        cursor: pointer; transition: all 0.15s;
    }
    .mb-wph-maint-reload-btn:hover { border-color: #d97706; color: #d97706; background: #fffbeb; }
    .mb-wph-maint-preview-note {
        display: flex; align-items: flex-start; gap: 10px;
        background: #fffbeb; border: 1px solid #fde68a;
        border-radius: 10px; padding: 12px 16px; margin-top: 10px;
    }
    .mb-wph-maint-preview-note p { margin: 0; font-size: 12.5px; color: #78350f; line-height: 1.6; }
    .mb-wph-maint-preview-note code { background: #fef3c7; padding: 1px 5px; border-radius: 4px; font-size: 11.5px; }
    
    /* Custom Date Time Picker CSS */
    .mb-wph-cal-day {
        width: 32px;
        height: 32px;
        line-height: 32px;
        text-align: center;
        font-size: 12px;
        color: #334155;
        border-radius: 50%;
        cursor: pointer;
        display: inline-block;
        transition: all 0.2s;
        font-weight: 600;
    }
    .mb-wph-cal-day:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .mb-wph-cal-day.selected {
        background: #3858e9 !important;
        color: #fff !important;
        font-weight: 700;
    }
    .mb-wph-cal-day.other-month {
        color: #cbd5e1;
    }
    .mb-wph-cal-day.weekend {
        color: #ef4444;
    }
    .mb-wph-cal-day.weekend.other-month {
        color: #fca5a5;
    }
    
    /* Scroll Switch Styling */
    .mb-wph-scroll-switch {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 20px;
    }
    .mb-wph-scroll-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .mb-wph-scroll-slider {
        position: absolute;
        cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #e2e8f0;
        border-radius: 20px;
        transition: .3s ease;
    }
    .mb-wph-scroll-slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .3s ease;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.15);
    }
    .mb-wph-scroll-switch input:checked + .mb-wph-scroll-slider {
        background: #3858e9;
    }
    .mb-wph-scroll-switch input:checked + .mb-wph-scroll-slider:before {
        transform: translateX(18px);
    }

    /* === WP Media Modal: CSS isolation — ngăn styles trang bảo trì ảnh hưởng modal === */
    .media-modal .attachment-filters,
    .media-modal .media-toolbar-secondary {
        display: flex !important;
        align-items: center !important;
        gap: 4px !important;
        flex-wrap: nowrap !important;
        white-space: nowrap !important;
    }
    .media-modal .attachment-filters select,
    .media-modal select {
        flex: 0 0 auto !important;
        width: auto !important;
        max-width: 220px !important;
        height: 28px !important;
        line-height: 1 !important;
        border: 1px solid #ddd !important;
        border-radius: 3px !important;
        padding: 0 4px !important;
        background-color: #fff !important;
        box-sizing: border-box !important;
        display: inline-block !important;
        vertical-align: middle !important;
        font-size: 12px !important;
    }
    .media-modal .attachment-details .setting input[type="text"],
    .media-modal .attachment-details .setting textarea,
    .media-modal .compat-field input[type="text"],
    .media-modal .compat-field textarea {
        width: calc(100% - 98px) !important;
        border: 1px solid #ddd !important;
        border-radius: 3px !important;
        padding: 4px 6px !important;
        font-size: 12px !important;
        background-color: #fff !important;
        box-sizing: border-box !important;
        color: #32373c !important;
    }
    .media-modal .attachment-details .setting span {
        width: 90px !important;
        min-width: 90px !important;
    }

    /* Stats period custom dropdown */
    .mb-wph-stats-dd { position: relative; display: inline-block; }
    .mb-wph-stats-dd-trigger {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 5px 10px 5px 10px;
        border: 1.5px solid #d1d5db; border-radius: 7px;
        background: #fff; cursor: pointer; font-size: 12.5px;
        font-weight: 600; color: #374151; font-family: inherit;
        transition: border-color .15s, box-shadow .15s;
        white-space: nowrap;
    }
    .mb-wph-stats-dd-trigger:hover { border-color: #3858e9; }
    .mb-wph-stats-dd-trigger.open {
        border-color: #3858e9;
        box-shadow: 0 0 0 3px rgba(56,88,233,.1);
    }
    .mb-wph-stats-dd-dot {
        width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; display: inline-block;
    }
    .mb-wph-stats-dd-dot.is-active { background: #22c55e; box-shadow: 0 0 0 2px rgba(34,197,94,.2); }
    .mb-wph-stats-dd-dot.is-inactive { background: #ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,.15); }
    .mb-wph-stats-dd-panel {
        display: none; position: absolute; right: 0; top: calc(100% + 5px);
        background: #fff; border: 1.5px solid #e2e8f0; border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,.12); z-index: 9999; min-width: 140px; overflow: hidden;
    }
    .mb-wph-stats-dd-panel.open { display: block; }
    .mb-wph-stats-dd-option {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 14px; font-size: 13px; font-weight: 500;
        color: #374151; cursor: pointer; transition: background .12s;
    }
    .mb-wph-stats-dd-option:hover { background: #f8fafc; }
    .mb-wph-stats-dd-option.selected { background: #eff6ff; color: #1d4ed8; font-weight: 600; }

    /* Stats grid responsive */
    @media (max-width: 900px) {
        #wpaap_stats_grid { grid-template-columns: repeat(2, 1fr) !important; }
    }
    @media (max-width: 520px) {
        #wpaap_stats_grid { grid-template-columns: 1fr 1fr !important; }
    }

    /* Template Cards Layouts */
    .mb-wph-grid-layout {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        align-items: start;
    }
    .mb-wph-grid-layout .mb-wph-tpl-card {
        width: auto;
    }
    .mb-wph-grid-layout .mb-wph-tpl-thumb {
        height: 162px !important;
        padding-top: 0 !important;
    }
    @media (max-width: 900px) {
        .mb-wph-grid-layout { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .mb-wph-grid-layout { grid-template-columns: 1fr; }
    }
    .mb-wph-scroll-layout {
        display: flex;
        overflow-x: auto;
        gap: 16px;
        padding-bottom: 12px;
        scrollbar-width: thin;
    }
    .mb-wph-scroll-layout .mb-wph-tpl-card {
        flex: 0 0 312px;
        max-width: 312px;
    }
    .mb-wph-tpl-card {
        background: #fff;
        border: 1.5px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        transition: all 0.2s ease;
        box-sizing: border-box;
        width: 312px;
    }
    .mb-wph-tpl-card:hover {
        border-color: #cbd5e1;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.04);
    }
    .mb-wph-tpl-card.selected {
        border-color: #3858e9;
        box-shadow: 0 4px 12px rgba(56, 88, 233, 0.08);
    }
    .mb-wph-tpl-card.selected:hover {
        border-color: #3858e9;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(56, 88, 233, 0.12);
    }
    #wpaap-maint-layout.maint-disabled { opacity: 0.4; pointer-events: none; user-select: none; transition: opacity 0.3s; }
    #wpaap-maint-stats-section.maint-disabled,
    #wpaap-maint-tpl-section.maint-disabled { opacity: 0.4; pointer-events: none; user-select: none; transition: opacity 0.3s; }
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

    <div class="wrap wpaap-wrap" style="margin: 20px auto 40px; padding: 0 20px;">

    <!-- Page Header -->
    <div class="mb-wph-maint-header">
        <div class="mb-wph-maint-header-left">
            <div class="mb-wph-maint-header-title-row">
                <div class="mb-wph-maint-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Chế độ Bảo trì Website', 'whp'); ?></h1>
            </div>
            <p style="margin:0;font-size:13.5px;color:#64748b;line-height:1.6;padding-left:58px;max-width:400px;"><?php esc_html_e('Khi bật, khách truy cập thấy trang bảo trì thay vì nội dung website. Tùy chỉnh giao diện và nội dung theo ý muốn.', 'whp'); ?></p>
            <div style="padding-left:58px;display:flex;align-items:center;gap:12px;">
                <div class="mb-wph-maint-toggle-wrap">
                    <label class="mb-toggle-switch">
                        <input type="checkbox" id="wpaap_maint_toggle" <?php checked($active, '1'); ?> value="1">
                        <span class="mb-toggle-slider"></span>
                    </label>
                    <span class="mb-wph-maint-toggle-label <?php echo $active ? 'active' : ''; ?>" id="wpaap_maint_toggle_label">
                        <?php echo $active ? esc_html__('Đang bật', 'whp') : esc_html__('Đang tắt', 'whp'); ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="mb-wph-maint-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="mg1" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#d97706" stop-opacity="0.11"/><stop offset="100%" stop-color="#fbbf24" stop-opacity="0.05"/></linearGradient>
                    <linearGradient id="mg2" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#d97706" stop-opacity="0.27"/><stop offset="100%" stop-color="#fbbf24" stop-opacity="0.13"/></linearGradient>
                    <linearGradient id="mg3" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#fbbf24" stop-opacity="0.38"/><stop offset="100%" stop-color="#d97706" stop-opacity="0.22"/></linearGradient>
                </defs>
                <!-- Soft background wave -->
                <path d="M290 168 Q390 92 492 122 Q572 148 680 72 L680 168 Z" fill="url(#mg1)"/>
                <!-- Large gear (cx=584, cy=82, r=38) -->
                <circle cx="584" cy="82" r="38" fill="url(#mg1)" stroke="#fde68a" stroke-width="1.5"/>
                <rect x="580" y="40" width="8" height="12" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8"/>
                <rect x="580" y="118" width="8" height="12" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8"/>
                <rect x="542" y="78" width="12" height="8" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8"/>
                <rect x="614" y="78" width="12" height="8" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8"/>
                <rect x="600" y="48" width="8" height="12" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8" transform="rotate(45,604,54)"/>
                <rect x="560" y="48" width="8" height="12" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8" transform="rotate(-45,564,54)"/>
                <rect x="600" y="108" width="8" height="12" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8" transform="rotate(-45,604,114)"/>
                <rect x="560" y="108" width="8" height="12" rx="3" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.8" transform="rotate(45,564,114)"/>
                <circle cx="584" cy="82" r="17" fill="white" fill-opacity="0.65" stroke="#fde68a" stroke-width="1.2"/>
                <circle cx="584" cy="82" r="7" fill="url(#mg2)"/>
                <!-- Small gear (cx=452, cy=50, r=20) -->
                <circle cx="452" cy="50" r="20" fill="url(#mg1)" stroke="#fde68a" stroke-width="1.2"/>
                <rect x="448" y="26" width="8" height="9" rx="2.5" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7"/>
                <rect x="448" y="65" width="8" height="9" rx="2.5" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7"/>
                <rect x="428" y="46" width="9" height="8" rx="2.5" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7"/>
                <rect x="467" y="46" width="9" height="8" rx="2.5" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7"/>
                <rect x="461" y="29" width="7" height="9" rx="2" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7" transform="rotate(45,464,33)"/>
                <rect x="432" y="29" width="7" height="9" rx="2" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7" transform="rotate(-45,436,33)"/>
                <rect x="461" y="62" width="7" height="9" rx="2" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7" transform="rotate(-45,464,66)"/>
                <rect x="432" y="62" width="7" height="9" rx="2" fill="url(#mg3)" stroke="#fde68a" stroke-width="0.7" transform="rotate(45,436,66)"/>
                <circle cx="452" cy="50" r="9" fill="white" fill-opacity="0.6" stroke="#fde68a" stroke-width="1"/>
                <circle cx="452" cy="50" r="3.5" fill="url(#mg2)"/>
                <!-- Wrench (diagonal top-center to gear) -->
                <path d="M498 26 C504 20 515 20 519 27 C523 34 521 42 516 46 L539 69 L547 77 C549 79 549 83 547 85 C545 87 541 87 539 85 L531 77 L508 54 C502 57 495 53 492 47 C489 40 491 32 498 26 Z" fill="url(#mg2)" stroke="#fde68a" stroke-width="1.3"/>
                <path d="M500 31 C503 27 510 27 513 31 C515 35 514 39 511 42" stroke="#fde68a" stroke-width="1.5" stroke-linecap="round" fill="none" stroke-opacity="0.45"/>
                <!-- Progress card -->
                <rect x="316" y="54" width="110" height="60" rx="10" fill="white" fill-opacity="0.55" stroke="#fde68a" stroke-width="1.2"/>
                <rect x="326" y="64" width="65" height="5" rx="2.5" fill="#fde68a" fill-opacity="0.6"/>
                <rect x="326" y="76" width="90" height="7" rx="3.5" fill="#fef3c7" fill-opacity="0.9"/>
                <rect x="326" y="76" width="60" height="7" rx="3.5" fill="url(#mg2)"/>
                <rect x="326" y="90" width="52" height="4" rx="2" fill="#fde68a" fill-opacity="0.45"/>
                <rect x="326" y="98" width="38" height="4" rx="2" fill="#fde68a" fill-opacity="0.3"/>
                <!-- Hard hat -->
                <path d="M424 140 Q424 120 441 116 Q449 114 457 116 Q474 120 474 140 Z" fill="url(#mg2)" stroke="#fde68a" stroke-width="1.3"/>
                <rect x="418" y="140" width="62" height="9" rx="4" fill="url(#mg2)" stroke="#fde68a" stroke-width="1.1"/>
                <line x1="426" y1="135" x2="472" y2="135" stroke="#fde68a" stroke-width="1.2" stroke-opacity="0.5"/>
                <!-- Clock -->
                <circle cx="636" cy="124" r="23" fill="url(#mg1)" stroke="#fde68a" stroke-width="1.3"/>
                <line x1="636" y1="113" x2="636" y2="124" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-opacity="0.6"/>
                <line x1="636" y1="124" x2="646" y2="130" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-opacity="0.6"/>
                <circle cx="636" cy="124" r="3" fill="#d97706" fill-opacity="0.5"/>
                <!-- Sparkles -->
                <path d="M382 22 L384 16 L386 22 L392 24 L386 26 L384 32 L382 26 L376 24 Z" fill="#fbbf24" fill-opacity="0.4"/>
                <path d="M536 17 L537.5 12 L539 17 L544 18.5 L539 20 L537.5 25 L536 20 L531 18.5 Z" fill="#fde68a" fill-opacity="0.5"/>
                <!-- Dots -->
                <circle cx="346" cy="32" r="5" fill="#fbbf24" fill-opacity="0.22"/>
                <circle cx="497" cy="150" r="5.5" fill="#d97706" fill-opacity="0.15"/>
                <circle cx="558" cy="152" r="4" fill="#fbbf24" fill-opacity="0.2"/>
                <circle cx="620" cy="42" r="4" fill="#fde68a" fill-opacity="0.35"/>
                <circle cx="660" cy="152" r="3" fill="#fbbf24" fill-opacity="0.18"/>
                <circle cx="318" cy="128" r="3" fill="#d97706" fill-opacity="0.15"/>
            </svg>
        </div>
    </div>

    <div id="wpaap-maint-layout" class="mb-wph-maint-layout<?php echo !$active ? ' maint-disabled' : ''; ?>">

        <!-- Cột trái: cài đặt -->
        <div>
            <!-- Form nội dung -->
            <div class="mb-wph-maint-card">
                <h3><span class="dashicons dashicons-superhero" style="color:#3858e9;font-size:17px;width:17px;height:17px;"></span> <?php esc_html_e('Nội dung trang bảo trì', 'whp'); ?></h3>

                <div class="mb-wph-maint-ai-row">
                    <p><?php esc_html_e('Chưa biết viết gì? Để AI tự động soạn nội dung phù hợp.', 'whp'); ?></p>
                    <button type="button" id="wpaap_maint_ai_btn" data-ai-ok="<?php echo $maint_ai_ok ? '1' : '0'; ?>">
                        <span class="dashicons dashicons-superhero" style="font-size:14px;width:14px;height:14px;line-height:14px;"></span>
                        <?php esc_html_e('Nhờ AI soạn', 'whp'); ?>
                    </button>
                </div>
                <div id="wpaap_maint_ai_notice"></div>

                <div class="mb-wph-maint-field">
                    <label for="wpaap_maint_title"><?php esc_html_e('Tiêu đề trang', 'whp'); ?> (&lt;title&gt;)</label>
                    <input type="text" id="wpaap_maint_title" placeholder="<?php esc_attr_e('Vd: Bảo trì hệ thống', 'whp'); ?>" value="<?php echo esc_attr($title); ?>">
                </div>
                <div class="mb-wph-maint-field">
                    <label for="wpaap_maint_heading"><?php esc_html_e('Tiêu đề chính', 'whp'); ?></label>
                    <input type="text" id="wpaap_maint_heading" placeholder="<?php esc_attr_e('Vd: Trang web đang nâng cấp!', 'whp'); ?>" value="<?php echo esc_attr($heading); ?>">
                </div>
                <div class="mb-wph-maint-field">
                    <label for="wpaap_maint_heading_sub"><?php esc_html_e('Tiêu đề phụ', 'whp'); ?></label>
                    <input type="text" id="wpaap_maint_heading_sub" placeholder="<?php esc_attr_e('Vd: Chúng tôi sẽ sớm quay lại', 'whp'); ?>" value="<?php echo esc_attr($heading_sub); ?>">
                </div>
                <div class="mb-wph-maint-field">
                    <label for="wpaap_maint_desc"><?php esc_html_e('Mô tả', 'whp'); ?></label>
                    <textarea id="wpaap_maint_desc" placeholder="<?php esc_attr_e('Vd: Website đang bảo trì. Vui lòng quay lại sau!', 'whp'); ?>"><?php echo esc_textarea($desc); ?></textarea>
                </div>
                <div class="mb-wph-maint-field">
                    <label><?php esc_html_e('Logo trang bảo trì', 'whp'); ?></label>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <input type="text" id="wpaap_maint_logo" placeholder="<?php esc_attr_e('Đường dẫn ảnh logo hoặc tải lên', 'whp'); ?>" value="<?php echo esc_url($logo); ?>" style="flex: 1;">
                        <button type="button" id="wpaap_maint_logo_upload_btn" class="button button-secondary" style="height: 38px; line-height: 36px; padding: 0 12px;"><?php esc_html_e('Tải ảnh lên', 'whp'); ?></button>
                    </div>
                    <div id="wpaap_maint_logo_preview_wrap" style="margin-top: 8px; <?php echo $logo ? '' : 'display: none;'; ?>">
                        <img id="wpaap_maint_logo_preview" src="<?php echo esc_url($logo); ?>" style="max-height: 80px; max-width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px; background: #f8fafc;">
                        <div style="margin-top: 4px;">
                            <a href="#" id="wpaap_maint_logo_remove_btn" style="color: #ef4444; font-size: 12px; text-decoration: none;"><span class="dashicons dashicons-trash" style="font-size: 14px; width: 14px; height: 14px; vertical-align: middle;"></span> <?php esc_html_e('Xóa logo', 'whp'); ?></a>
                        </div>
                    </div>
                </div>
                <div class="mb-wph-maint-field" style="position: relative;">
                    <label for="wpaap_maint_countdown"><?php esc_html_e('Thời gian kết thúc bảo trì (Đếm ngược)', 'whp'); ?></label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="text" id="wpaap_maint_countdown_display" readonly placeholder="dd/mm/yyyy --:-- --" value="<?php echo esc_attr($countdown ? date('d/m/Y H:i', strtotime($countdown)) : ''); ?>" style="padding: 10px 38px 10px 38px; width: 100%; cursor: pointer; background: #f8fafc;">
                        <span class="dashicons dashicons-calendar-alt" style="position: absolute; left: 12px; color: #64748b; font-size: 16px; width: 16px; height: 16px; pointer-events: none;"></span>
                        <button type="button" id="wpaap_maint_countdown_clear" title="<?php esc_attr_e('Xóa thời gian', 'whp'); ?>" style="position: absolute; right: 10px; background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 18px; line-height: 1; padding: 0; display: <?php echo $countdown ? 'flex' : 'none'; ?>; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%;">&times;</button>
                        <input type="hidden" id="wpaap_maint_countdown" value="<?php echo esc_attr($countdown); ?>">
                    </div>
                    
                    <!-- Custom Date Time Picker Popup -->
                    <div id="wpaap_maint_picker_popup" style="display: none; position: absolute; top: calc(100% + 5px); left: 0; z-index: 1000; background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); padding: 16px; width: 440px; gap: 16px; font-family: inherit; box-sizing: border-box;">
                        
                        <!-- Left: Choose Date -->
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <span style="font-weight: 700; font-size: 14px; color: #0f172a;"><?php esc_html_e('Chọn ngày', 'whp'); ?></span>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" id="prev-month" style="background: none; border: none; cursor: pointer; color: #64748b; padding: 2px;"><span class="dashicons dashicons-arrow-left-alt2" style="font-size: 14px; width: 14px; height: 14px;"></span></button>
                                    <button type="button" id="next-month" style="background: none; border: none; cursor: pointer; color: #64748b; padding: 2px;"><span class="dashicons dashicons-arrow-right-alt2" style="font-size: 14px; width: 14px; height: 14px;"></span></button>
                                </div>
                            </div>
                            
                            <div id="picker-month-year" style="font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 8px; text-transform: capitalize;">Tháng 5 2026</div>
                            
                            <!-- Week days header (H B T N S B C) -->
                            <div style="display: grid; grid-template-columns: repeat(7, 1fr); text-align: center; font-size: 11px; font-weight: 700; color: #64748b; margin-bottom: 6px;">
                                <span>H</span><span>B</span><span>T</span><span>N</span><span>S</span><span>B</span><span>C</span>
                            </div>
                            
                            <!-- Days grid -->
                            <div id="picker-days-grid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center;">
                                <!-- Dynamically populated -->
                            </div>
                        </div>
                        
                        <!-- Divider line -->
                        <div style="width: 1px; background: #e2e8f0; align-self: stretch;"></div>
                        
                        <!-- Right: Choose Time -->
                        <div style="width: 150px; display: flex; flex-direction: column;">
                            <span style="font-weight: 700; font-size: 14px; color: #0f172a; margin-bottom: 12px; display: block;"><?php esc_html_e('Chọn giờ', 'whp'); ?></span>
                            
                            <div style="display: flex; gap: 6px; flex: 1; align-items: center; justify-content: center;">
                                <!-- Hour select wheel/arrows -->
                                <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                                    <button type="button" id="hour-up" style="background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 10px; line-height: 1;"><span class="dashicons dashicons-arrow-up-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span></button>
                                    <div id="selected-hour" style="font-size: 18px; font-weight: 700; color: #0f172a; padding: 6px 0;">09</div>
                                    <button type="button" id="hour-down" style="background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 10px; line-height: 1;"><span class="dashicons dashicons-arrow-down-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span></button>
                                </div>
                                
                                <span style="font-weight: 700; font-size: 18px; color: #94a3b8; padding-bottom: 4px;">:</span>
                                
                                <!-- Minute select wheel/arrows -->
                                <div style="display: flex; flex-direction: column; align-items: center; flex: 1;">
                                    <button type="button" id="minute-up" style="background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 10px; line-height: 1;"><span class="dashicons dashicons-arrow-up-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span></button>
                                    <div id="selected-minute" style="font-size: 18px; font-weight: 700; color: #0f172a; padding: 6px 0;">43</div>
                                    <button type="button" id="minute-down" style="background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 10px; line-height: 1;"><span class="dashicons dashicons-arrow-down-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span></button>
                                </div>
                                
                                <!-- AM/PM select -->
                                <div style="display: flex; flex-direction: column; align-items: center; width: 44px; margin-left: 4px;">
                                    <button type="button" id="ampm-toggle-up" style="background: none; border: none; cursor: pointer; color: #94a3b8;"><span class="dashicons dashicons-arrow-up-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span></button>
                                    <div id="selected-ampm" style="font-size: 14px; font-weight: 700; color: #0f172a; padding: 6px 0; background: #f1f5f9; width: 100%; text-align: center; border-radius: 6px; letter-spacing: 0.5px;">SA</div>
                                    <button type="button" id="ampm-toggle-down" style="background: none; border: none; cursor: pointer; color: #94a3b8;"><span class="dashicons dashicons-arrow-down-alt2" style="font-size: 12px; width: 12px; height: 12px;"></span></button>
                                </div>
                            </div>
                            
                            <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <button type="button" id="picker-clear" style="background: none; border: 1px solid #fca5a5; color: #ef4444; font-size: 12px; font-weight: 600; cursor: pointer; padding: 4px 10px; border-radius: 6px;"><?php esc_html_e('Xóa', 'whp'); ?></button>
                                <div style="display: flex; gap: 8px;">
                                    <button type="button" id="picker-cancel" style="background: none; border: none; color: #64748b; font-size: 12px; font-weight: 600; cursor: pointer; padding: 4px 8px;"><?php esc_html_e('Hủy', 'whp'); ?></button>
                                    <button type="button" id="picker-confirm" style="background: #3858e9; border: none; color: #fff; font-size: 12px; font-weight: 600; cursor: pointer; padding: 6px 12px; border-radius: 6px;"><?php esc_html_e('Chọn', 'whp'); ?></button>
                                </div>
                            </div>
                        </div>
                        
                    </div>

                    <div style="font-size: 11.5px; color: #1e293b; margin-top: 8px; display: flex; align-items: flex-start; gap: 6px; background: #f0fdfa; padding: 8px 12px; border-radius: 6px; border-left: 3px solid #0d9488; line-height: 1.4;">
                        <span class="dashicons dashicons-info" style="font-size: 15px; width: 15px; height: 15px; color: #0d9488; margin-top: 1px; flex-shrink: 0;"></span>
                        <span><?php esc_html_e('Để trống trường này nếu bạn không muốn hiển thị đồng hồ đếm ngược trên giao diện bảo trì.', 'whp'); ?></span>
                    </div>
                </div>

                <!-- Mạng xã hội & Liên hệ -->
                <div class="mb-wph-maint-field" style="margin-top:20px;padding-top:18px;border-top:1px solid #f1f5f9;">
                    <label style="font-size:13px;font-weight:700;color:#0f172a;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                        <span class="dashicons dashicons-share" style="color:#3858e9;font-size:16px;width:16px;height:16px;"></span>
                        <?php esc_html_e('Liên hệ', 'whp'); ?> &amp; <?php esc_html_e('Mạng xã hội', 'whp'); ?>
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div>
                            <label for="wpaap_maint_phone" style="font-size:12px;font-weight:600;color:#475569;display:flex;align-items:center;gap:5px;margin-bottom:5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <?php esc_html_e('Số điện thoại', 'whp'); ?>
                            </label>
                            <input type="text" id="wpaap_maint_phone" placeholder="Vd: 0987 654 321" value="<?php echo esc_attr($social_phone); ?>">
                        </div>
                        <div>
                            <label for="wpaap_maint_email" style="font-size:12px;font-weight:600;color:#475569;display:flex;align-items:center;gap:5px;margin-bottom:5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                Email
                            </label>
                            <input type="text" id="wpaap_maint_email" placeholder="Vd: contact@example.com" value="<?php echo esc_attr($social_email); ?>">
                        </div>
                        <div>
                            <label for="wpaap_maint_facebook" style="font-size:12px;font-weight:600;color:#475569;display:flex;align-items:center;gap:5px;margin-bottom:5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </label>
                            <input type="text" id="wpaap_maint_facebook" placeholder="URL trang Facebook" value="<?php echo esc_attr($social_facebook); ?>">
                        </div>
                        <div>
                            <label for="wpaap_maint_youtube" style="font-size:12px;font-weight:600;color:#475569;display:flex;align-items:center;gap:5px;margin-bottom:5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#FF0000"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube
                            </label>
                            <input type="text" id="wpaap_maint_youtube" placeholder="<?php esc_attr_e('URL kênh YouTube', 'whp'); ?>" value="<?php echo esc_attr($social_youtube); ?>">
                        </div>
                        <div>
                            <label for="wpaap_maint_zalo" style="font-size:12px;font-weight:600;color:#475569;display:flex;align-items:center;gap:5px;margin-bottom:5px;">
                                <svg width="14" height="14" viewBox="0 0 40 40"><circle cx="20" cy="20" r="20" fill="#0068FF"/><text x="20" y="26" text-anchor="middle" fill="#fff" font-size="18" font-weight="bold" font-family="Arial">Z</text></svg>
                                Zalo
                            </label>
                            <input type="text" id="wpaap_maint_zalo" placeholder="<?php esc_attr_e('Số Zalo hoặc zalo.me/...', 'whp'); ?>" value="<?php echo esc_attr($social_zalo); ?>">
                        </div>
                        <div>
                            <label for="wpaap_maint_tiktok" style="font-size:12px;font-weight:600;color:#475569;display:flex;align-items:center;gap:5px;margin-bottom:5px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="#000"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.82a8.18 8.18 0 0 0 4.78 1.53V6.9a4.85 4.85 0 0 1-1-.21z"/></svg>
                                TikTok
                            </label>
                            <input type="text" id="wpaap_maint_tiktok" placeholder="URL TikTok" value="<?php echo esc_attr($social_tiktok); ?>">
                        </div>
                    </div>
                    <p style="font-size:11.5px;color:#94a3b8;margin-top:10px;"><?php esc_html_e('Để trống ô nào để ẩn biểu tượng đó trên trang bảo trì.', 'whp'); ?></p>
                </div>

                <div id="wpaap-maint-save-bar" class="mb-wph-maint-save-bar">
                    <div class="mb-wph-maint-save-bar-note">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="#3858e9" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        <?php esc_html_e('Các thay đổi sẽ được áp dụng ngay sau khi lưu.', 'whp'); ?>
                    </div>
                    <button type="button" id="wpaap_maint_save_btn">
                        <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
                        <?php esc_html_e('Lưu cài đặt', 'whp'); ?>
                    </button>
                </div>
                <div id="wpaap_maint_notice"></div>
            </div>
        </div>

        <!-- Cột phải: xem trước -->
        <div class="mb-wph-maint-preview-panel">
            <div class="mb-wph-maint-preview-card">
                <div class="mb-wph-maint-preview-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <h3><?php esc_html_e('Xem trước trang bảo trì', 'whp'); ?></h3>
                        <p><?php esc_html_e('Giao diện trang bảo trì hiển thị với khách truy cập', 'whp'); ?></p>
                    </div>
                    <a href="<?php echo esc_url($preview_url); ?>" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#fff;border:1.5px solid #fde68a;border-radius:8px;color:#d97706;font-size:12.5px;font-weight:600;text-decoration:none;white-space:nowrap;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <?php esc_html_e('Mở tab mới', 'whp'); ?>
                    </a>
                </div>
                <div class="mb-wph-maint-preview-window">
                    <!-- Browser chrome (desktop mode) -->
                    <div class="mb-wph-browser-bar" id="mb_maint_browser_bar">
                        <div class="mb-wph-browser-dots"><span></span><span></span><span></span></div>
                        <div class="mb-wph-browser-url">
                            <svg width="10" height="11" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span><?php echo esc_html(parse_url(get_site_url(), PHP_URL_HOST)); ?></span>
                        </div>
                    </div>
                    <div class="mb-wph-maint-preview-frame-wrap" id="mb_maint_frame_wrap">
                        <div class="mb-wph-maint-preview-skeleton" id="mb_maint_skeleton">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            <span><?php esc_html_e('Đang tải preview...', 'whp'); ?></span>
                        </div>
                        <iframe id="wpaap_maint_preview_frame" src="<?php echo esc_url($preview_url); ?>"
                                scrolling="no"></iframe>
                    </div>
                </div>
            </div>

            <!-- Info note -->
            <div class="mb-wph-maint-preview-note">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                <p><?php esc_html_e('Preview hiển thị đúng giao diện bảo trì với khách truy cập.', 'whp'); ?><br><?php esc_html_e('Khi bảo trì', 'whp'); ?> <strong><?php esc_html_e('BẬT', 'whp'); ?></strong>: <?php esc_html_e('admin truy cập bình thường, chỉ khách bị chặn.', 'whp'); ?></p>
            </div>

            <!-- Checklist trước khi bật -->
            <div style="margin-top:14px;background:#fff;border:1.5px solid #bbf7d0;border-left:4px solid #16a34a;border-radius:12px;padding:16px 18px;">
                <div style="display:flex;align-items:center;gap:8px;padding-bottom:10px;margin-bottom:12px;border-bottom:1px solid #dcfce7;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;background:#dcfce7;border-radius:7px;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    <span style="font-size:14px;font-weight:700;color:#15803d;"><?php esc_html_e('Checklist trước khi bật', 'whp'); ?></span>
                </div>
                <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:7px;">
                    <?php
                    $checks = [
                        ['ok' => !empty($heading),                         'text' => __('Đã đặt tiêu đề trang', 'whp')],
                        ['ok' => !empty($desc),                            'text' => __('Đã điền mô tả / thông báo', 'whp')],
                        ['ok' => !empty($countdown),                       'text' => __('Đã đặt đồng hồ đếm ngược', 'whp')],
                        ['ok' => !empty($social_phone) || !empty($social_email), 'text' => __('Đã thêm thông tin liên hệ', 'whp')],
                        ['ok' => !empty($logo),                            'text' => __('Đã tải logo lên', 'whp')],
                    ];
                    foreach ($checks as $chk):
                        $color = $chk['ok'] ? '#16a34a' : '#94a3b8';
                        $bg    = $chk['ok'] ? '#f0fdf4' : '#f8fafc';
                        $icon  = $chk['ok']
                            ? '<polyline points="20 6 9 17 4 12"/>'
                            : '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>';
                    ?>
                    <li style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:<?php echo $bg; ?>;border-radius:7px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;background:<?php echo $chk['ok'] ? '#dcfce7' : '#f1f5f9'; ?>;border-radius:50%;flex-shrink:0;">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="<?php echo $color; ?>" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><?php echo $icon; ?></svg>
                        </span>
                        <span style="font-size:12.5px;font-weight:500;color:<?php echo $chk['ok'] ? '#15803d' : '#64748b'; ?>;"><?php echo esc_html($chk['text']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Mẹo SEO & kỹ thuật -->
            <div style="margin-top:14px;background:#fff;border:1.5px solid #bfdbfe;border-left:4px solid #3b82f6;border-radius:12px;padding:16px 18px;">
                <div style="display:flex;align-items:center;gap:8px;padding-bottom:10px;margin-bottom:12px;border-bottom:1px solid #dbeafe;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;background:#dbeafe;border-radius:7px;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/></svg>
                    </span>
                    <span style="font-size:14px;font-weight:700;color:#1d4ed8;"><?php esc_html_e('Lưu ý kỹ thuật', 'whp'); ?></span>
                </div>
                <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:8px;">
                    <?php
                    $tips = [
                        ['icon' => '#3b82f6', 'svg' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
                         'text' => sprintf(__('Google nhận mã %s — hiểu trang tạm nghỉ, không ảnh hưởng SEO.', 'whp'), '<strong>503</strong>')],
                        ['icon' => '#8b5cf6', 'svg' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                         'text' => __('Admin đã đăng nhập luôn truy cập được trang bình thường.', 'whp')],
                        ['icon' => '#f59e0b', 'svg' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                         'text' => __('Đặt đồng hồ đếm ngược để khách biết thời gian quay lại.', 'whp')],
                    ];
                    foreach ($tips as $t):
                    ?>
                    <li style="display:flex;align-items:flex-start;gap:9px;">
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#f8fafc;border-radius:6px;flex-shrink:0;margin-top:1px;">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="<?php echo $t['icon']; ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $t['svg']; ?></svg>
                        </span>
                        <span style="font-size:12px;color:#475569;line-height:1.55;"><?php echo $t['text']; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

    </div>

    <!-- Thống kê truy cập khi bảo trì -->
    <div id="wpaap-maint-stats-section" class="<?php echo !$active ? 'maint-disabled' : ''; ?>" style="margin-top:24px;">
        <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:14px;padding:22px 24px;box-shadow:0 2px 8px rgba(0,0,0,.05);">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;border-bottom:1px solid #f1f5f9;padding-bottom:14px;">
                <h3 style="margin:0;font-size:15px;font-weight:700;color:#0f172a;display:flex;align-items:center;gap:9px;">
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </span>
                    <?php esc_html_e('Thống kê truy cập khi bảo trì', 'whp'); ?>
                </h3>
                <div class="mb-wph-stats-dd" id="wpaap_stats_dd">
                    <button type="button" class="mb-wph-stats-dd-trigger" id="wpaap_stats_trigger">
                        <span class="mb-wph-stats-dd-dot is-active" id="wpaap_stats_dot"></span>
                        <span id="wpaap_stats_label"><?php esc_html_e('Hôm nay', 'whp'); ?></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div class="mb-wph-stats-dd-panel" id="wpaap_stats_panel">
                        <div class="mb-wph-stats-dd-option selected" data-value="today" data-label="<?php esc_attr_e('Hôm nay', 'whp'); ?>">
                            <span class="mb-wph-stats-dd-dot is-active"></span> <?php esc_html_e('Hôm nay', 'whp'); ?>
                        </div>
                        <div class="mb-wph-stats-dd-option" data-value="yesterday" data-label="<?php esc_attr_e('Hôm qua', 'whp'); ?>">
                            <span class="mb-wph-stats-dd-dot is-inactive"></span> <?php esc_html_e('Hôm qua', 'whp'); ?>
                        </div>
                        <div class="mb-wph-stats-dd-option" data-value="7" data-label="<?php esc_attr_e('7 ngày qua', 'whp'); ?>">
                            <span class="mb-wph-stats-dd-dot is-inactive"></span> <?php esc_html_e('7 ngày qua', 'whp'); ?>
                        </div>
                        <div class="mb-wph-stats-dd-option" data-value="30" data-label="<?php esc_attr_e('30 ngày qua', 'whp'); ?>">
                            <span class="mb-wph-stats-dd-dot is-inactive"></span> <?php esc_html_e('30 ngày qua', 'whp'); ?>
                        </div>
                    </div>
                    <input type="hidden" id="wpaap_stats_days" value="today">
                </div>
            </div>

            <div id="wpaap_stats_grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
                <?php
                $stat_cards = [
                    ['id' => 'stat_hits',     'label' => __('Lượt truy cập', 'whp'),  'icon_color' => '#6366f1', 'bg' => '#eef2ff', 'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
                    ['id' => 'stat_pv',       'label' => __('Lượt xem trang', 'whp'), 'icon_color' => '#f59e0b', 'bg' => '#fffbeb', 'icon' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'],
                    ['id' => 'stat_unique',   'label' => __('Khách truy cập', 'whp'), 'icon_color' => '#10b981', 'bg' => '#ecfdf5', 'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                    ['id' => 'stat_bounce',   'label' => __('Tỷ lệ thoát', 'whp'),   'icon_color' => '#ef4444', 'bg' => '#fef2f2', 'icon' => '<polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/>'],
                ];
                foreach ($stat_cards as $c): ?>
                <div style="background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px 18px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                        <span style="font-size:13px;font-weight:600;color:#64748b;"><?php echo esc_html($c['label']); ?></span>
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:<?php echo esc_attr($c['bg']); ?>;border-radius:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="<?php echo esc_attr($c['icon_color']); ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?php echo $c['icon']; ?></svg>
                        </span>
                    </div>
                    <div id="<?php echo esc_attr($c['id']); ?>_val" style="font-size:26px;font-weight:800;color:#0f172a;line-height:1;margin-bottom:8px;">—</div>
                    <div id="<?php echo esc_attr($c['id']); ?>_pct" style="font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:4px;">
                        <span style="color:#94a3b8;"><?php esc_html_e('vs. kỳ trước', 'whp'); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <p id="wpaap_stats_empty" style="display:none;text-align:center;color:#94a3b8;font-size:13px;padding:24px 0;margin:0;">
                <?php esc_html_e('Chưa có dữ liệu. Bật bảo trì để bắt đầu ghi nhận lượt truy cập.', 'whp'); ?>
            </p>
        </div>
    </div>

    <!-- Chọn mẫu trang bảo trì -->
    <div id="wpaap-maint-tpl-section" class="<?php echo !$active ? 'maint-disabled' : ''; ?>" style="margin-top:28px;">
        <div style="background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:22px 24px;box-shadow:0 2px 6px rgba(0,0,0,.04);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:12px;">
                <h3 style="font-size:14.5px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center;gap:8px;">
                    <span class="dashicons dashicons-images-alt2" style="color:#3858e9;font-size:17px;width:17px;height:17px;"></span>
                    <?php esc_html_e('Mẫu giao diện', 'whp'); ?>
                </h3>
            </div>
            
            <div id="mb-wph-tpl-container" class="mb-wph-grid-layout">

                <?php
                $templates = [
                    'dark'         => ['label' => __('Mẫu Số 1', 'whp'), 'desc' => __('Phong Cách Tối & Hiện Đại', 'whp')],
                    'light'        => ['label' => __('Mẫu Số 2', 'whp'), 'desc' => __('Phong Cách Tối Giản', 'whp')],
                    'gradient'     => ['label' => __('Mẫu Số 3', 'whp'), 'desc' => __('Phong Cách Công Nghệ AI', 'whp')],
                    'construction' => ['label' => __('Mẫu Số 4', 'whp'), 'desc' => __('Phong Cách Vũ Trụ & Đang Dựng', 'whp')],
                    'corporate'    => ['label' => __('Mẫu Số 5', 'whp'), 'desc' => __('Phong Cách Doanh Nghiệp', 'whp')],
                    'cyberpunk'    => ['label' => __('Mẫu Số 6', 'whp'), 'desc' => __('Phong Cách Cyberpunk Neon', 'whp')],
                ];
                $tpl_bgs = ['dark' => '#090d16', 'light' => '#f8fafc', 'gradient' => '#1a1a2e', 'construction' => '#0a0813', 'corporate' => '#f4f6f9', 'cyberpunk' => '#0b0f19'];
                foreach ($templates as $key => $info):
                    $is_active  = ($tpl === $key);
                    $thumb_bg   = $tpl_bgs[$key] ?? '#111';
                    $thumb_url  = esc_url($preview_url . '&wpaap_tpl_preview=' . $key);
                    
                    $display_title = $info['label'];
                    if ($is_active) {
                        $display_title .= ' (' . __('Hiện Tại', 'whp') . ')';
                    }
                    $display_title .= ' - ' . $info['desc'];
                ?>
                <div class="mb-wph-tpl-card <?php echo $is_active ? 'selected' : ''; ?>" data-tpl="<?php echo esc_attr($key); ?>" style="cursor: pointer;">
                    <!-- Thumbnail wrapper -->
                    <div style="border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; position: relative; background: <?php echo esc_attr($thumb_bg); ?>;">
                        <!-- Thumbnail — iframe scaled via JS -->
                        <div class="mb-wph-tpl-thumb" data-tpl="<?php echo esc_attr($key); ?>"
                             style="width:100%; padding-top:62.5%; overflow:hidden; position:relative; cursor:pointer;">
                            <iframe src="<?php echo $thumb_url; ?>"
                                    style="width:1440px; height:900px; border:none;
                                           transform-origin:top left;
                                           pointer-events:none;
                                           position:absolute; left:0; top:0;"
                                    scrolling="no" loading="lazy" tabindex="-1"></iframe>
                        </div>
                    </div>
                    
                    <!-- Info row -->
                    <div style="margin-top:10px;">
                        <div class="mb-wph-tpl-title-text" style="font-size:12px; font-weight:600; color:#334155; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-bottom:8px;" title="<?php echo esc_attr($display_title); ?>">
                            <?php echo esc_html($display_title); ?>
                        </div>
                        <div style="display:flex; gap:6px;">
                            <button type="button" class="mb-custom-tpl-btn" data-tpl="<?php echo esc_attr($key); ?>"
                                style="flex:1; background:#fff; color:#3858e9; border:1.5px solid #c7d2fe; border-radius:6px; padding:5px 8px; font-size:11.5px; font-weight:600; cursor:pointer; white-space:nowrap; transition:all 0.2s; display:inline-flex; align-items:center; justify-content:center; gap:4px;">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <?php esc_html_e('Xem demo', 'whp'); ?>
                            </button>
                            <?php if ($is_active): ?>
                            <button type="button" class="mb-apply-tpl-btn" data-tpl="<?php echo esc_attr($key); ?>" disabled
                                style="flex:1; background:#f0fdf4; color:#16a34a; border:1px solid #86efac; border-radius:6px; padding:5px 8px; font-size:11.5px; font-weight:600; cursor:default; white-space:nowrap;">
                                ✓ <?php esc_html_e('Đang dùng', 'whp'); ?>
                            </button>
                            <?php else: ?>
                            <button type="button" class="mb-apply-tpl-btn" data-tpl="<?php echo esc_attr($key); ?>"
                                style="flex:1; background:#3858e9; color:#fff; border:none; border-radius:6px; padding:5px 8px; font-size:11.5px; font-weight:600; cursor:pointer; white-space:nowrap; transition:all 0.2s;">
                                <?php esc_html_e('Sử dụng', 'whp'); ?>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>

    </div><!-- /.wpaap-wrap -->

    <input type="hidden" id="wpaap_maint_nonce" value="<?php echo esc_attr($nonce); ?>">
    <input type="hidden" id="wpaap_maint_preview_url" value="<?php echo $preview_url; ?>">
    <input type="hidden" id="wpaap_maint_tpl" value="<?php echo esc_attr($tpl); ?>">

    <script>
    var whpMaintI18n = {
        on:             '<?php echo esc_js( __( 'Đang bật', 'whp' ) ); ?>',
        off:            '<?php echo esc_js( __( 'Đang tắt', 'whp' ) ); ?>',
        toastOn:        '<?php echo esc_js( __( 'Đã bật chế độ bảo trì', 'whp' ) ); ?>',
        toastOff:       '<?php echo esc_js( __( 'Đã tắt chế độ bảo trì', 'whp' ) ); ?>',
        toastErr:       '<?php echo esc_js( __( 'Lỗi lưu trạng thái', 'whp' ) ); ?>',
        toastConn:      '<?php echo esc_js( __( 'Lỗi kết nối', 'whp' ) ); ?>',
        saving:         '<?php echo esc_js( __( 'Đang lưu...', 'whp' ) ); ?>',
        savedOn:        '<?php echo esc_js( __( 'Đã lưu thành công! Chế độ bảo trì đang BẬT.', 'whp' ) ); ?>',
        savedOff:       '<?php echo esc_js( __( '✓ Đã lưu thành công.  ⚠ Chế độ bảo trì đang TẮT — website vẫn hoạt động bình thường. Bật toggle để kích hoạt bảo trì.', 'whp' ) ); ?>',
        saveFailed:     '<?php echo esc_js( __( 'Lưu thất bại.', 'whp' ) ); ?>',
        connError:      '<?php echo esc_js( __( 'Lỗi kết nối, vui lòng thử lại.', 'whp' ) ); ?>',
        aiNotConnected: '<?php echo esc_js( sprintf( __( '⚠️ Chưa kết nối AI. Vui lòng cấu hình API Key tại <a href="?page=mb-wphelper-ai&subtab=connection" style="color:#dc2626;text-decoration:underline;">tab Kết nối AI</a> trước.', 'whp' ) ) ); ?>',
        aiWriting:      '<?php echo esc_js( __( 'AI đang soạn...', 'whp' ) ); ?>',
        aiDone:         '<?php echo esc_js( __( 'AI đã soạn xong! Chỉnh lại nếu cần rồi nhấn Lưu.', 'whp' ) ); ?>',
        aiBadFormat:    '<?php echo esc_js( __( 'Dữ liệu AI không đúng định dạng. Thử lại.', 'whp' ) ); ?>',
        aiNoJson:       '<?php echo esc_js( __( 'Không tìm thấy JSON từ AI. Thử lại.', 'whp' ) ); ?>',
        aiNoResp:       '<?php echo esc_js( __( 'AI không phản hồi. Kiểm tra tab Kết nối AI.', 'whp' ) ); ?>',
        serverError:    '<?php echo esc_js( __( 'Lỗi kết nối máy chủ.', 'whp' ) ); ?>',
        tplApplied:     '<?php echo esc_js( __( 'Đã áp dụng mẫu thành công!', 'whp' ) ); ?>',
        inUse:          '<?php echo esc_js( __( '✓ Đang dùng', 'whp' ) ); ?>',
        useThis:        '<?php echo esc_js( __( 'Sử dụng', 'whp' ) ); ?>',
        vsPrev:         '<?php echo esc_js( __( 'vs. kỳ trước', 'whp' ) ); ?>'
    };
    jQuery(document).ready(function($) {

        var _maintSaveBtnHtml = $('#wpaap_maint_save_btn').html();

        // Scale iframe thumbnails to fill card width dynamically
        function scaleTplThumbs() {
            $('#mb-wph-tpl-container .mb-wph-tpl-thumb').each(function() {
                var wrap  = this;
                var scale = wrap.offsetWidth / 1440;
                var iframe = wrap.querySelector('iframe');
                if (iframe) iframe.style.transform = 'scale(' + scale + ')';
            });
        }
        scaleTplThumbs();
        $(window).on('resize', scaleTplThumbs);

        function whpToast(msg, type) {
            type = type || 'success';
            var wrap = document.getElementById('whp-toast-wrap');
            var t = document.createElement('div');
            t.className = 'whp-toast wt-' + type;
            t.innerHTML = '<div class="whp-toast-icon">' + (type === 'success' ? '✓' : '✕') + '</div><div class="whp-toast-msg">' + msg + '</div><button class="whp-toast-close" onclick="this.parentNode.remove()">×</button>';
            wrap.appendChild(t);
            setTimeout(function() { t.classList.add('wt-out'); setTimeout(function() { t.remove(); }, 280); }, 3000);
        }

        var _maintToggleNonce = '<?php echo esc_js(wp_create_nonce('whp_maintenance_toggle')); ?>';

        // Set initial visual state for maintenance layout
        (function() {
            var on = $('#wpaap_maint_toggle').is(':checked');
            $('#wpaap-maint-layout').toggleClass('maint-disabled', !on);
            $('#wpaap-maint-stats-section').toggleClass('maint-disabled', !on);
            $('#wpaap-maint-tpl-section').toggleClass('maint-disabled', !on);
            $('#wpaap-maint-save-bar').css('display', on ? '' : 'none');
        })();

        // Toggle label + dim + AJAX save
        $('#wpaap_maint_toggle').on('change', function() {
            var on = $(this).is(':checked');
            $('#wpaap_maint_toggle_label').text(on ? whpMaintI18n.on : whpMaintI18n.off).toggleClass('active', on);
            $('#wpaap-maint-layout').toggleClass('maint-disabled', !on);
            $('#wpaap-maint-stats-section').toggleClass('maint-disabled', !on);
            $('#wpaap-maint-tpl-section').toggleClass('maint-disabled', !on);
            $('#wpaap-maint-save-bar').css('display', on ? '' : 'none');
            var fd = new FormData();
            fd.append('action', 'whp_maintenance_toggle_enable');
            fd.append('nonce', _maintToggleNonce);
            fd.append('active', on ? '1' : '0');
            fetch(wpaap_ajax.ajax_url, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        whpToast(on ? whpMaintI18n.toastOn : whpMaintI18n.toastOff, 'success');
                    } else {
                        whpToast(whpMaintI18n.toastErr, 'error');
                    }
                })
                .catch(function() { whpToast(whpMaintI18n.toastConn, 'error'); });
        });

        // Reload preview iframe (debounced)
        var previewTimer;
        function reloadPreview() {
            clearTimeout(previewTimer);
            previewTimer = setTimeout(function() {
                var frame = document.getElementById('wpaap_maint_preview_frame');
                var skeleton = document.getElementById('mb_maint_skeleton');
                if (frame) {
                    frame.classList.remove('loaded');
                    if (skeleton) skeleton.classList.remove('hidden');
                    var baseUrl = $('#wpaap_maint_preview_url').val();
                    frame.src = baseUrl + '&_cb=' + Date.now();
                }
            }, 800);
        }

        // Scale desktop preview iframe to fit container
        function scalePreviewFrame() {
            var wrap = document.getElementById('mb_maint_frame_wrap');
            var frame = document.getElementById('wpaap_maint_preview_frame');
            if (!wrap || !frame) return;
            var wrapW = wrap.offsetWidth;
            var desktopW = 1440, desktopH = 950;
            var scale = wrapW / desktopW;
            frame.style.width  = desktopW + 'px';
            frame.style.height = desktopH + 'px';
            frame.style.transform = 'scale(' + scale + ')';
            frame.style.transformOrigin = 'top left';
            frame.style.left = '0';
        }

        scalePreviewFrame();
        $(window).on('resize', scalePreviewFrame);

        // Show desktop iframe, hide skeleton after load
        (function() {
            var frame = document.getElementById('wpaap_maint_preview_frame');
            var skeleton = document.getElementById('mb_maint_skeleton');
            function onFrameLoad() {
                if (frame) frame.classList.add('loaded');
                if (skeleton) skeleton.classList.add('hidden');
            }
            if (frame) {
                frame.addEventListener('load', onFrameLoad);
                if (frame.contentDocument && frame.contentDocument.readyState === 'complete') onFrameLoad();
            }
        })();

        // Lưu cài đặt
        $('#wpaap_maint_save_btn').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="font-size:15px;width:15px;height:15px;line-height:15px;display:inline-block;animation:wpaap-rotate-cw 1s linear infinite;"></span> ' + whpMaintI18n.saving);

            $.ajax({
                url: wpaap_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpaap_save_maintenance',
                    nonce: $('#wpaap_maint_nonce').val(),
                    whp_maintenance_template:    $('#wpaap_maint_tpl').val(),
                    whp_maintenance_active:      $('#wpaap_maint_toggle').is(':checked') ? '1' : '',
                    whp_maintenance_title:       $('#wpaap_maint_title').val(),
                    whp_maintenance_heading:     $('#wpaap_maint_heading').val(),
                    whp_maintenance_heading_sub: $('#wpaap_maint_heading_sub').val(),
                    whp_maintenance_desc:        $('#wpaap_maint_desc').val(),
                    whp_maintenance_logo:        $('#wpaap_maint_logo').val(),
                    whp_maintenance_countdown:   $('#wpaap_maint_countdown').val(),
                    whp_maintenance_phone:       $('#wpaap_maint_phone').val(),
                    whp_maintenance_email:       $('#wpaap_maint_email').val(),
                    whp_maintenance_facebook:    $('#wpaap_maint_facebook').val(),
                    whp_maintenance_youtube:     $('#wpaap_maint_youtube').val(),
                    whp_maintenance_zalo:        $('#wpaap_maint_zalo').val(),
                    whp_maintenance_tiktok:      $('#wpaap_maint_tiktok').val(),
                },
                success: function(res) {
                    var $notice = $('#wpaap_maint_notice');
                    if (res.success) {
                        var isActive = res.data && res.data.maintenance_active;
                        if (isActive) {
                            $notice.removeClass('error mb-error warning').addClass('success')
                                .text(whpMaintI18n.savedOn).show();
                            setTimeout(function() { $notice.fadeOut(); }, 3000);
                        } else {
                            $notice.removeClass('success error mb-error').addClass('warning')
                                .html(whpMaintI18n.savedOff).show();
                            setTimeout(function() { $notice.fadeOut(); }, 7000);
                        }
                        reloadPreview();
                    } else {
                        $notice.removeClass('success warning').addClass('error mb-error').text(res.data.message || whpMaintI18n.saveFailed).show();
                        setTimeout(function() { $notice.fadeOut(); }, 3000);
                    }
                },
                error: function() {
                    $('#wpaap_maint_notice').removeClass('success').addClass('error mb-error').text(whpMaintI18n.connError).show();
                },
                complete: function() {
                    $btn.prop('disabled', false).html(_maintSaveBtnHtml);
                }
            });
        });

        // Nhờ AI soạn nội dung
        $('#wpaap_maint_ai_btn').on('click', function() {
            var $btn = $(this);
            var $notice = $('#wpaap_maint_ai_notice');

            // Kiểm tra kết nối AI trước khi gọi AJAX (data-ai-ok baked from PHP, không phụ thuộc wpaap_ajax)
            if ($btn.data('ai-ok') !== 1 && $btn.data('ai-ok') !== '1') {
                $notice.removeClass('success warning').addClass('error mb-error')
                    .html(whpMaintI18n.aiNotConnected)
                    .show();
                setTimeout(function() { $notice.fadeOut(); }, 8000);
                return;
            }

            $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="font-size:14px;width:14px;height:14px;line-height:14px;display:inline-block;animation:wpaap-rotate-cw 1s linear infinite;"></span> ' + whpMaintI18n.aiWriting);

            var siteName = '<?php echo esc_js(get_bloginfo('name')); ?>';
            var prompt = 'Soạn nội dung trang bảo trì cho website "' + siteName + '". Trả lời ĐÚNG định dạng JSON, không thêm bất kỳ chữ nào ngoài JSON:\n{"title":"...","heading":"...","heading_sub":"...","desc":"..."}';

            $.ajax({
                url: wpaap_ajax.ajax_url,
                type: 'POST',
                data: { action: 'wpaap_chat_ai', nonce: wpaap_ajax.nonce, prompt: prompt },
                success: function(res) {
                    var $notice = $('#wpaap_maint_ai_notice');
                    if (res.success && res.data.response) {
                        var match = res.data.response.trim().match(/\{[\s\S]*\}/);
                        if (match) {
                            try {
                                var data = JSON.parse(match[0]);
                                if (data.title)       $('#wpaap_maint_title').val(data.title);
                                if (data.heading)     $('#wpaap_maint_heading').val(data.heading);
                                if (data.heading_sub) $('#wpaap_maint_heading_sub').val(data.heading_sub);
                                if (data.desc)        $('#wpaap_maint_desc').val(data.desc);
                                $notice.removeClass('error mb-error').addClass('success').text(whpMaintI18n.aiDone).show();
                            } catch(e) {
                                $notice.removeClass('success').addClass('error mb-error').text(whpMaintI18n.aiBadFormat).show();
                            }
                        } else {
                            $notice.removeClass('success').addClass('error mb-error').text(whpMaintI18n.aiNoJson).show();
                        }
                    } else {
                        var errMsg = (res.data && res.data.message) ? res.data.message : whpMaintI18n.aiNoResp;
                        $notice.removeClass('success').addClass('error mb-error').text(errMsg).show();
                    }
                    setTimeout(function() { $notice.fadeOut(); }, 7000);
                },
                error: function(xhr) {
                    var $notice = $('#wpaap_maint_ai_notice');
                    var errMsg = whpMaintI18n.serverError;
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errMsg = xhr.responseJSON.data.message;
                    } else if (xhr.responseText) {
                        try {
                            var parsed = JSON.parse(xhr.responseText);
                            if (parsed.data && parsed.data.message) {
                                errMsg = parsed.data.message;
                            }
                        } catch(e) {}
                    }
                    $notice.removeClass('success').addClass('error mb-error').text(errMsg).show();
                    setTimeout(function() { $notice.fadeOut(); }, 7000);
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-superhero" style="font-size:14px;width:14px;height:14px;line-height:14px;"></span> Nhờ AI soạn');
                }
            });
        });

        // Media upload — luôn mở tab "Tất cả tập tin", dùng _.defer để chuyển mode
        // 1 lần duy nhất, tránh double re-render gây mất panel Chi tiết tập đính kèm
        $('#wpaap_maint_logo_upload_btn').on('click', function(e) {
            e.preventDefault();

            var frame = wp.media({
                title: '<?php echo esc_js( __( 'Chọn logo trang bảo trì', 'whp' ) ); ?>',
                library: { type: 'image' },
                multiple: false,
                button: { text: '<?php echo esc_js( __( 'Chọn làm logo', 'whp' ) ); ?>' }
            });

            // Chuyển sang browse mode 1 lần sau khi frame hoàn tất render
            // _.defer = setTimeout(fn, 0) — chạy sau call stack hiện tại
            frame.on('open', function() {
                _.defer(function() {
                    try {
                        if (frame.content.mode() !== 'browse') {
                            frame.content.mode('browse');
                        }
                    } catch(e2) {}
                });
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $('#wpaap_maint_logo').val(attachment.url);
                $('#wpaap_maint_logo_preview').attr('src', attachment.url);
                $('#wpaap_maint_logo_preview_wrap').show();
            });

            frame.open();
        });

        // Xóa logo
        $('#wpaap_maint_logo_remove_btn').on('click', function(e) {
            e.preventDefault();
            $('#wpaap_maint_logo').val('');
            $('#wpaap_maint_logo_preview_wrap').hide();
        });

        // Tự gõ link logo cũng update preview
        $('#wpaap_maint_logo').on('input', function() {
            var val = $(this).val();
            if (val) {
                $('#wpaap_maint_logo_preview').attr('src', val);
                $('#wpaap_maint_logo_preview_wrap').show();
            } else {
                $('#wpaap_maint_logo_preview_wrap').hide();
            }
        });

        // ----------------- CUSTOM DATE-TIME PICKER LOGIC -----------------
        var currentDate = new Date();
        var selectedDate = null;
        
        // Parse existing countdown value
        var existingVal = $('#wpaap_maint_countdown').val();
        if (existingVal) {
            selectedDate = new Date(existingVal);
            if (!isNaN(selectedDate.getTime())) {
                currentDate = new Date(selectedDate.getTime());
            } else {
                selectedDate = null;
            }
        }

        // Initialize state variables for time
        var selHour = 9;
        var selMin = 43;
        var selAmpm = "SA";

        if (selectedDate) {
            var h24 = selectedDate.getHours();
            selMin = selectedDate.getMinutes();
            if (h24 >= 12) {
                selAmpm = "CH";
                selHour = h24 === 12 ? 12 : h24 - 12;
            } else {
                selAmpm = "SA";
                selHour = h24 === 0 ? 12 : h24;
            }
        }

        // Update time display inputs
        function updateTimeDisplay() {
            $('#selected-hour').text(String(selHour).padStart(2, '0'));
            $('#selected-minute').text(String(selMin).padStart(2, '0'));
            $('#selected-ampm').text(selAmpm);
        }

        // Render Calendar
        function renderCalendar() {
            var year = currentDate.getFullYear();
            var month = currentDate.getMonth(); // 0-indexed

            var monthsVi = ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"];
            $('#picker-month-year').text(monthsVi[month] + " " + year);

            var firstDay = new Date(year, month, 1);
            var startDayOfWeek = firstDay.getDay(); // 0 (Sun), 1 (Mon), ..., 6 (Sat)
            var startCol = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;

            var daysInMonth = new Date(year, month + 1, 0).getDate();
            var daysInPrevMonth = new Date(year, month, 0).getDate();

            var $grid = $('#picker-days-grid');
            $grid.empty();

            // Previous month trailing days
            for (var i = startCol - 1; i >= 0; i--) {
                var dayNum = daysInPrevMonth - i;
                appendDayCell(dayNum, true, month - 1, year);
            }

            // Current month days
            for (var d = 1; d <= daysInMonth; d++) {
                appendDayCell(d, false, month, year);
            }

            // Next month leading days
            var totalCellsSoFar = startCol + daysInMonth;
            var remainingCells = 42 - totalCellsSoFar;
            for (var n = 1; n <= remainingCells; n++) {
                appendDayCell(n, true, month + 1, year);
            }
        }

        function appendDayCell(day, isOtherMonth, m, y) {
            var cellMonth = m;
            var cellYear = y;
            if (cellMonth < 0) {
                cellMonth = 11;
                cellYear--;
            } else if (cellMonth > 11) {
                cellMonth = 0;
                cellYear++;
            }

            var cellDate = new Date(cellYear, cellMonth, day);
            var dow = cellDate.getDay(); // 0 is Sun, 6 is Sat
            var isWeekend = (dow === 0 || dow === 6);

            var isSelected = selectedDate && 
                             selectedDate.getDate() === day && 
                             selectedDate.getMonth() === cellMonth && 
                             selectedDate.getFullYear() === cellYear;

            var classes = "mb-wph-cal-day";
            if (isOtherMonth) classes += " other-month";
            if (isWeekend) classes += " weekend";
            if (isSelected) classes += " selected";

            var $cell = $('<span class="' + classes + '">' + day + '</span>');
            $cell.data('day', day);
            $cell.data('month', cellMonth);
            $cell.data('year', cellYear);

            $cell.on('click', function() {
                $('.mb-wph-cal-day').removeClass('selected');
                $(this).addClass('selected');
                selectedDate = new Date($(this).data('year'), $(this).data('month'), $(this).data('day'));
            });

            $('#picker-days-grid').append($cell);
        }

        // Hour Up/Down
        $('#hour-up').on('click', function() {
            selHour++;
            if (selHour > 12) selHour = 1;
            updateTimeDisplay();
        });
        $('#hour-down').on('click', function() {
            selHour--;
            if (selHour < 1) selHour = 12;
            updateTimeDisplay();
        });

        // Minute Up/Down
        $('#minute-up').on('click', function() {
            selMin++;
            if (selMin > 59) selMin = 0;
            updateTimeDisplay();
        });
        $('#minute-down').on('click', function() {
            selMin--;
            if (selMin < 0) selMin = 59;
            updateTimeDisplay();
        });

        // AM/PM Up/Down
        $('#ampm-toggle-up, #ampm-toggle-down').on('click', function() {
            selAmpm = selAmpm === "SA" ? "CH" : "SA";
            updateTimeDisplay();
        });

        // Month Prev/Next
        $('#prev-month').on('click', function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
        $('#next-month').on('click', function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        // Clear countdown (cả từ popup lẫn nút × trên input)
        function clearCountdown() {
            $('#wpaap_maint_countdown').val('');
            $('#wpaap_maint_countdown_display').val('');
            $('#wpaap_maint_countdown_clear').hide();
            selectedDate = null;
            $('#wpaap_maint_picker_popup').css('display', 'none');
        }
        $('#picker-clear').on('click', clearCountdown);
        $('#wpaap_maint_countdown_clear').on('click', function(e) {
            e.stopPropagation();
            clearCountdown();
        });

        // Cancel
        $('#picker-cancel').on('click', function() {
            $('#wpaap_maint_picker_popup').css('display', 'none');
        });

        // Confirm Selection
        $('#picker-confirm').on('click', function() {
            if (!selectedDate) {
                selectedDate = new Date();
            }
            
            var h24 = selHour;
            if (selAmpm === "CH") {
                h24 = h24 === 12 ? 12 : h24 + 12;
            } else {
                h24 = h24 === 12 ? 0 : h24;
            }

            selectedDate.setHours(h24);
            selectedDate.setMinutes(selMin);
            selectedDate.setSeconds(0);

            // Format raw string for input value: YYYY-MM-DDTHH:MM
            var y = selectedDate.getFullYear();
            var m = String(selectedDate.getMonth() + 1).padStart(2, '0');
            var d = String(selectedDate.getDate()).padStart(2, '0');
            var hr = String(selectedDate.getHours()).padStart(2, '0');
            var mn = String(selectedDate.getMinutes()).padStart(2, '0');

            var rawVal = y + "-" + m + "-" + d + "T" + hr + ":" + mn;
            $('#wpaap_maint_countdown').val(rawVal);

            // Format display string: DD/MM/YYYY HH:MM AM/PM
            var displayVal = d + "/" + m + "/" + y + " " + String(selHour).padStart(2, '0') + ":" + mn + " " + selAmpm;
            $('#wpaap_maint_countdown_display').val(displayVal);
            $('#wpaap_maint_countdown_clear').show();

            $('#wpaap_maint_picker_popup').css('display', 'none');
        });

        // Open Popup
        $('#wpaap_maint_countdown_display').on('click', function(e) {
            e.stopPropagation();
            renderCalendar();
            updateTimeDisplay();
            var $popup = $('#wpaap_maint_picker_popup');
            if ($popup.css('display') === 'none') {
                $popup.css('display', 'flex');
            } else {
                $popup.css('display', 'none');
            }
        });

        // Prevent closing popup when clicking inside it
        $('#wpaap_maint_picker_popup').on('click', function(e) {
            e.stopPropagation();
        });

        // Close on clicking outside
        $(document).on('click', function() {
            $('#wpaap_maint_picker_popup').css('display', 'none');
        });

        // Xử lý Chọn template card — click vào card (không phải nút) để preview
        $(document).on('click', '.mb-wph-tpl-card', function(e) {
            if ($(e.target).closest('.mb-apply-tpl-btn, .mb-custom-tpl-btn').length) return;
            var tpl = $(this).data('tpl');
            // Preview trên iframe bên phải (không lưu DB)
            var baseUrl = $('#wpaap_maint_preview_url').val();
            document.getElementById('wpaap_maint_preview_frame').src = baseUrl + '&wpaap_tpl_preview=' + encodeURIComponent(tpl);
        });

        // Nút Xem demo — load template vào preview rồi scroll lên xem
        $(document).on('click', '.mb-custom-tpl-btn', function(e) {
            e.stopPropagation();
            var tpl     = $(this).data('tpl');
            var baseUrl = $('#wpaap_maint_preview_url').val();
            var frame   = document.getElementById('wpaap_maint_preview_frame');
            var skeleton = document.getElementById('mb_maint_skeleton');
            if (frame) {
                frame.classList.remove('loaded');
                if (skeleton) skeleton.classList.remove('hidden');
                frame.src = baseUrl + '&wpaap_tpl_preview=' + encodeURIComponent(tpl);
            }
            var $panel = $('.mb-wph-maint-preview-card');
            if ($panel.length) {
                $('html, body').animate({ scrollTop: $panel.offset().top - 40 }, 500);
            }
        });

        // Nút Sử dụng — lưu ngay lập tức
        $(document).on('click', '.mb-apply-tpl-btn', function(e) {
            e.stopPropagation();
            var tpl = $(this).data('tpl');
            $('#wpaap_maint_tpl').val(tpl);
            var $btn = $(this);
            $btn.prop('disabled', true).text(whpMaintI18n.saving);

            $.ajax({
                url: wpaap_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wpaap_save_maintenance',
                    nonce: $('#wpaap_maint_nonce').val(),
                    whp_maintenance_template:    tpl,
                    whp_maintenance_active:      $('#wpaap_maint_toggle').is(':checked') ? '1' : '',
                    whp_maintenance_title:       $('#wpaap_maint_title').val(),
                    whp_maintenance_heading:     $('#wpaap_maint_heading').val(),
                    whp_maintenance_heading_sub: $('#wpaap_maint_heading_sub').val(),
                    whp_maintenance_desc:        $('#wpaap_maint_desc').val(),
                    whp_maintenance_logo:        $('#wpaap_maint_logo').val(),
                    whp_maintenance_countdown:   $('#wpaap_maint_countdown').val(),
                    whp_maintenance_phone:       $('#wpaap_maint_phone').val(),
                    whp_maintenance_email:       $('#wpaap_maint_email').val(),
                    whp_maintenance_facebook:    $('#wpaap_maint_facebook').val(),
                    whp_maintenance_youtube:     $('#wpaap_maint_youtube').val(),
                    whp_maintenance_zalo:        $('#wpaap_maint_zalo').val(),
                    whp_maintenance_tiktok:      $('#wpaap_maint_tiktok').val(),
                },
                success: function(res) {
                    var $notice = $('#wpaap_maint_notice');
                    if (res.success) {
                        $notice.removeClass('error mb-error').addClass('success')
                            .html('<span class="dashicons dashicons-yes" style="vertical-align:middle;margin-right:4px;"></span> ' + whpMaintI18n.tplApplied)
                            .show();
                        // Cập nhật trạng thái tất cả nút "Sử dụng" trên cards
                        $('.mb-apply-tpl-btn').each(function() {
                            var $b = $(this);
                            if ($b.data('tpl') === tpl) {
                                $b.prop('disabled', true)
                                  .text(whpMaintI18n.inUse)
                                  .css({background:'#f0fdf4', color:'#16a34a', border:'1px solid #86efac', cursor:'default'});
                            } else {
                                $b.prop('disabled', false)
                                  .text(whpMaintI18n.useThis)
                                  .css({background:'#3858e9', color:'#fff', border:'none', cursor:'pointer'});
                            }
                        });
                        $('.mb-wph-tpl-card').removeClass('selected');
                        $('.mb-wph-tpl-card[data-tpl="' + tpl + '"]').addClass('selected');
                        reloadPreview();
                    } else {
                        $notice.removeClass('success').addClass('error mb-error').text(res.data.message || whpMaintI18n.saveFailed).show();
                    }
                    setTimeout(function() { $notice.fadeOut(); }, 3000);
                },
                error: function() {
                    $('#wpaap_maint_notice').removeClass('success').addClass('error mb-error').text(whpMaintI18n.connError).show();
                    setTimeout(function() { $('#wpaap_maint_notice').fadeOut(); }, 3000);
                },
                complete: function() {
                    if ($btn.text() !== whpMaintI18n.inUse) {
                        $btn.prop('disabled', false).text(whpMaintI18n.useThis);
                    }
                }
            });
        });

    // ── Maintenance stats ──────────────────────────────────────────────────
    function wpaapFmtNum(n) {
        if (n === undefined || n === null) return '—';
        return n.toLocaleString('vi-VN');
    }
    function wpaapFmtPct(p, invert) {
        if (p === undefined || p === null) return '';
        var up   = invert ? p < 0 : p > 0;
        var down = invert ? p > 0 : p < 0;
        var abs  = Math.abs(p).toFixed(1);
        var arrow = up ? '↑' : (down ? '↓' : '');
        var color = up ? '#16a34a' : (down ? '#dc2626' : '#94a3b8');
        return '<span style="color:' + color + ';">' + arrow + ' ' + abs + '%</span> <span style="color:#94a3b8;">' + whpMaintI18n.vsPrev + '</span>';
    }

    function wpaapLoadStats(period) {
        var $grid  = $('#wpaap_stats_grid');
        var $empty = $('#wpaap_stats_empty');
        $grid.css('opacity', '0.4');
        $.post(ajaxurl, {
            action:  'wpaap_get_maint_stats',
            nonce:   $('#wpaap_maint_nonce').val(),
            period:  period
        }, function(res) {
            $grid.css('opacity', '1');
            if (!res.success) return;
            var d = res.data;
            var hasData = (d.hits + d.unique) > 0;
            $empty.toggle(!hasData);
            $grid.toggle(hasData || true); // always show grid frame

            $('#stat_hits_val').text(wpaapFmtNum(d.hits));
            $('#stat_hits_pct').html(wpaapFmtPct(d.hits_pct, false));

            $('#stat_pv_val').text(wpaapFmtNum(d.pageviews));
            $('#stat_pv_pct').html(wpaapFmtPct(d.pv_pct, false));

            $('#stat_unique_val').text(wpaapFmtNum(d.unique));
            $('#stat_unique_pct').html(wpaapFmtPct(d.unique_pct, false));

            $('#stat_bounce_val').text(d.bounce.toFixed(1) + '%');
            $('#stat_bounce_pct').html(wpaapFmtPct(d.bounce_pct, true)); // invert: lower bounce = good (green)
        });
    }

    wpaapLoadStats('today');

    // Custom dropdown logic
    $('#wpaap_stats_trigger').on('click', function(e) {
        e.stopPropagation();
        $(this).toggleClass('open');
        $('#wpaap_stats_panel').toggleClass('open');
    });
    $(document).on('click', function() {
        $('#wpaap_stats_trigger').removeClass('open');
        $('#wpaap_stats_panel').removeClass('open');
    });
    $(document).on('click', '.mb-wph-stats-dd-option', function(e) {
        e.stopPropagation();
        var val   = $(this).data('value');
        var label = $(this).data('label');
        // Update selected state
        $('.mb-wph-stats-dd-option').removeClass('selected');
        $(this).addClass('selected');
        // Dots: selected = green, others = red
        $('.mb-wph-stats-dd-option .mb-wph-stats-dd-dot')
            .removeClass('is-active').addClass('is-inactive');
        $(this).find('.mb-wph-stats-dd-dot')
            .removeClass('is-inactive').addClass('is-active');
        // Update trigger
        $('#wpaap_stats_label').text(label);
        $('#wpaap_stats_dot').removeClass('is-inactive').addClass('is-active');
        $('#wpaap_stats_days').val(val);
        // Close + load
        $('#wpaap_stats_trigger').removeClass('open');
        $('#wpaap_stats_panel').removeClass('open');
        wpaapLoadStats(val);
    });

    });
    </script>
    <?php
}
