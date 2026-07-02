<?php if (!defined('ABSPATH')) exit; ?>
<?php whp_get_shared('header'); ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
/* ==============================
   Kênh liên hệ - Redesigned V2
   ============================== */
.mb-wph-page {
    font-family: inherit;
    max-width: 1200px;
    margin: 20px auto 40px;
    padding: 0 15px;
    box-sizing: border-box;
}

/* Header Card */
.mb-wph-header-card {
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
.mb-wph-header-left {
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
.mb-wph-header-right {
    position: absolute;
    inset: 0 0 0 38%;
    overflow: hidden;
    pointer-events: none;
}
.mb-wph-header-right img {
    width: 100%; height: 100%;
    object-fit: cover;
    object-position: left center;
}
.mb-wph-header-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mb-wph-header-icon-box {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3858e9, #6b8af5);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(56,88,233,0.3);
}
.mb-wph-page-header { display: flex; align-items: center; gap: 12px; }
.mb-wph-page-subtitle {
    color: #64748b; font-size: 13.5px; line-height: 1.6;
    margin: 0; padding-left: 58px; max-width: 400px;
}
.mb-wph-header-toggle-inline {
    display: inline-flex; align-items: center; gap: 10px; padding-left: 58px;
}
.mb-wph-feature-bar { display: none; }

/* Grid layout */
.mb-contact-layout-wrapper {
    display: grid;
    grid-template-columns: 1.8fr 1.2fr;
    gap: 24px;
    align-items: start;
}
@media (max-width: 991px) {
    .mb-contact-layout-wrapper {
        grid-template-columns: 1fr;
    }
}

/* Cards styling */
.mb-wph-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 20px;
    overflow: hidden;
}
.mb-wph-card-inner {
    padding: 24px;
}

/* Accent borders on the left */
.mb-wph-section-card {
    border-left: 4px solid transparent;
    transition: box-shadow 0.2s;
}
.mb-wph-section-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.05), 0 0 0 1px #e8edf3;
}
.mb-wph-section-card.accent-blue   { border-left-color: #3858e9; }
.mb-wph-section-card.accent-orange { border-left-color: #f97316; }
.mb-wph-section-card.accent-green  { border-left-color: #00c217; }
.mb-wph-section-card.accent-purple { border-left-color: #8b5cf6; }
.mb-wph-section-card.accent-darkblue { border-left-color: #1d4ed8; }

/* Section Header */
.mb-wph-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 20px;
}
.mb-wph-section-header-left {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.mb-wph-section-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    line-height: 1;
}
.accent-blue     .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(56,88,233,0.18), 0 0 0 10px rgba(56,88,233,0.08); }
.accent-orange   .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(249,115,22,0.18), 0 0 0 10px rgba(249,115,22,0.08); }
.accent-green    .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(0,194,23,0.18),  0 0 0 10px rgba(0,194,23,0.08); }
.accent-purple   .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(139,92,246,0.18),0 0 0 10px rgba(139,92,246,0.08); }
.accent-darkblue .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(29,78,216,0.18), 0 0 0 10px rgba(29,78,216,0.08); }

.mb-wph-section-header-text h3 {
    margin: 0 0 4px 0;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}
.mb-wph-section-header-text p {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.5;
}

/* Modern iOS toggle switch */
.mb-wph-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
}
.mb-wph-switch input { opacity: 0; width: 0; height: 0; }
.mb-wph-slider {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 30px;
    cursor: pointer;
    transition: background 0.25s;
}
.mb-wph-slider::after {
    content: '';
    position: absolute;
    width: 22px;
    height: 22px;
    background: #fff;
    border-radius: 50%;
    left: 3px;
    top: 50%;
    transform: translateY(-50%);
    transition: transform 0.25s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}
.mb-wph-switch input:checked + .mb-wph-slider {
    background: #22c55e;
}
.mb-wph-switch input:checked + .mb-wph-slider::after {
    transform: translateY(-50%) translateX(24px);
}
.mb-wph-switch.mini { width: 48px; height: 24px; }
.mb-wph-switch.mini .mb-wph-slider::after {
    width: 18px;
    height: 18px;
}
.mb-wph-switch.mini input:checked + .mb-wph-slider::after {
    transform: translateY(-50%) translateX(24px);
}
.mb-wph-toggle-status {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
    transition: color 0.2s;
}
.mb-wph-toggle-status.active { color: #22c55e; }

/* Form fields & counters */
.mb-wph-field {
    margin-bottom: 16px;
}
.mb-wph-field:last-child { margin-bottom: 0; }
.mb-wph-field > label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}
.mb-wph-field input[type="text"],
.mb-wph-field input[type="number"] {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    color: #1e293b;
    background: #fff;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}
.mb-wph-field select {
    width: 100% !important;
    max-width: 100% !important;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    color: #1e293b;
    background: #fff;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}
.mb-wph-field input:focus,
.mb-wph-field select:focus {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.1);
}
.mb-field-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.mb-wph-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
    flex-shrink: 0;
    display: inline-block;
}
.mb-wph-custom-dropdown {
    position: relative;
    user-select: none;
}
.mb-wph-custom-dropdown-trigger {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 10px 12px;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
    font-size: 13.5px;
    color: #1e293b;
}
.mb-wph-custom-dropdown-trigger:hover { border-color: #94a3b8; }
.mb-wph-custom-dropdown.open .mb-wph-custom-dropdown-trigger {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.1);
}
.mb-wph-custom-dropdown-trigger span.label { flex: 1; }
.mb-wph-custom-dropdown-trigger .chevron {
    color: #94a3b8;
    transition: transform 0.2s;
    flex-shrink: 0;
}
.mb-wph-custom-dropdown.open .chevron { transform: rotate(180deg); }
.mb-wph-custom-dropdown-menu {
    display: none;
    position: fixed;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    z-index: 999999;
    min-width: 120px;
}
.mb-wph-custom-dropdown.open .mb-wph-custom-dropdown-menu { display: block; }
.mb-wph-custom-dropdown-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    cursor: pointer;
    font-size: 13.5px;
    color: #1e293b;
    transition: background 0.15s;
    border-radius: 0;
}
.mb-wph-custom-dropdown-option:first-child { border-radius: 8px 8px 0 0; }
.mb-wph-custom-dropdown-option:last-child  { border-radius: 0 0 8px 8px; }
.mb-wph-custom-dropdown-option:only-child  { border-radius: 8px; }
.mb-wph-custom-dropdown-option:hover { background: #f8fafc; }
.mb-wph-custom-dropdown-option.selected { background: #eff2fe; color: #3858e9; font-weight: 600; }
.mb-wph-custom-dropdown.centered .mb-wph-custom-dropdown-trigger { justify-content: space-between; }
.mb-wph-custom-dropdown.centered .mb-wph-custom-dropdown-trigger span.label { flex: 1; text-align: center; }
.wph-option-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ef4444;
    flex-shrink: 0;
    display: inline-block;
    transition: background 0.2s;
}
.mb-wph-custom-dropdown-option.selected .wph-option-dot { background: #22c55e; }

/* Input group prefixes */
.wph-input-group {
    display: flex;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.wph-input-group:focus-within {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.1);
}
.wph-input-addon {
    background: #f1f5f9;
    color: #475569;
    padding: 0 16px;
    font-size: 12.5px;
    font-weight: 500;
    border-right: 1px solid #cbd5e1;
    display: flex;
    align-items: center;
    white-space: nowrap;
    user-select: none;
    width: 160px;
    box-sizing: border-box;
    flex-shrink: 0;
}
.wph-input-group input {
    border: none !important;
    background: transparent !important;
    flex: 1;
    height: 38px;
    padding: 0 14px !important;
    font-size: 13.5px !important;
    outline: none !important;
    box-shadow: none !important;
    margin: 0 !important;
}

/* Premium Segmented Control */
.mb-wph-segmented-control {
    display: flex;
    background: #f1f5f9;
    padding: 3px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    height: 40px;
    box-sizing: border-box;
}
.mb-wph-segmented-control label.mb-wph-segment {
    flex: 1 !important;
    text-align: center;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px;
    user-select: none;
    margin: 0 !important;
    padding: 0 !important;
    height: 100% !important;
}
.mb-wph-segmented-control label.mb-wph-segment.active {
    background: #fff !important;
    color: #3858e9 !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 0 0 1px rgba(0,0,0,0.02);
}

/* Range Wrapper */
.mb-wph-range-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
    height: 40px;
}
.mb-wph-range-wrapper input[type="range"] {
    flex: 1;
    -webkit-appearance: none;
    appearance: none;
    height: 6px;
    border-radius: 3px;
    background: #cbd5e1;
    outline: none;
    cursor: pointer;
}
.mb-wph-range-wrapper input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #3858e9;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    transition: transform 0.1s;
}
.mb-wph-range-wrapper input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.15);
}
.mb-wph-range-value {
    font-size: 12.5px;
    font-weight: 700;
    color: #3858e9;
    background: #eff2fe;
    padding: 4px 10px;
    border-radius: 6px;
    min-width: 38px;
    text-align: center;
    border: 1px solid #a5b4fc;
}

/* Premium Color Input Wrapper */
.mb-color-input-wrapper {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 0 10px;
    height: 40px;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.mb-color-input-wrapper:focus-within {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.1);
}
.mb-color-indicator {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 1px solid rgba(0,0,0,0.1);
    position: relative;
    cursor: pointer;
    flex-shrink: 0;
}
.mb-color-indicator input[type="color"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    padding: 0;
    border: none;
}
.mb-color-input-wrapper input[type="text"] {
    flex: 1;
    border: none !important;
    background: transparent !important;
    padding: 0 8px !important;
    height: 100% !important;
    font-size: 13.5px !important;
    font-weight: 600 !important;
    color: #1e293b !important;
    outline: none !important;
    box-shadow: none !important;
}

/* Agent List styling */
.agent-list-wrapper {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
}
.agent-list-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    transition: all 0.2s;
}
.agent-list-row:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border-color: #cbd5e1;
}
.agent-btn-action {
    background: none;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-edit-agent { color: #3858e9; }
.btn-edit-agent:hover { background: #eff2fe; border-color: #a5b4fc; }
.btn-delete-agent { color: #ef4444; border-color: #fca5a5; }
.btn-delete-agent:hover { background: #fef2f2; border-color: #f87171; }

#btnAddMorePhone {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #0068ff;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
#btnAddMorePhone:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}

/* Save Bar */
.mb-wph-save-bar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 24px;
}
.mb-wph-save-note {
    font-size: 12.5px;
    color: #64748b;
}
.mb-wph-save-btn {
    background: #3858e9;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 24px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.mb-wph-save-btn:hover {
    background: #2563eb;
}

/* Modal Styling */
.wph-modal {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.wph-modal-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
}
.wph-modal-content {
    position: relative;
    background: #fff;
    border-radius: 16px;
    width: 420px;
    max-width: 90%;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    animation: wphModalShow 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    overflow: hidden;
}
@keyframes wphModalShow {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.wph-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.wph-modal-header h4 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
}
.wph-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #94a3b8;
    cursor: pointer;
    line-height: 1;
}
.wph-modal-close:hover { color: #475569; }
.wph-modal-body {
    padding: 20px;
}
.wph-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 14px 20px;
    border-top: 1px solid #f1f5f9;
    background: #f8fafc;
}
.wph-btn {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}
.wph-btn-secondary {
    background: #fff;
    color: #475569;
    border: 1px solid #cbd5e1;
}
.wph-btn-secondary:hover { background: #f1f5f9; }
.wph-btn-primary {
    background: #3858e9;
    color: #fff;
}
.wph-btn-primary:hover { background: #2563eb; }

/* Avatar Radio Selector in Modal */
.form-avatar-group {
    display: flex;
    gap: 14px;
    margin-top: 8px;
}
.form-avatar-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: #64748b;
    cursor: pointer;
}
.form-avatar-item label {
    cursor: pointer;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    border: 2px solid #e2e8f0 !important;
    overflow: hidden !important;
    transition: all 0.2s;
    background: #fff !important;
    margin: 0 !important;
    padding: 0 !important;
}
.form-avatar-item input[type="radio"] {
    display: none;
}
.form-avatar-item:has(input[type="radio"]:checked) label {
    border-color: #3858e9 !important;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.15) !important;
}
.form-avatar-item img { width: 34px; height: 34px; object-fit: contain; }

/* Preview Column Card details */
#wph-admin-preview-container {
    background: linear-gradient(145deg, #ede8f9 0%, #ddd4f5 40%, #cfc2f0 100%);
    border: 1px solid #c4b5eb;
    border-radius: 16px;
    padding: 32px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
    min-height: 340px;
}
#wph-admin-preview-container::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 140px; height: 140px;
    background: radial-gradient(circle, rgba(255,255,255,0.35) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
#wph-admin-preview-container::after {
    content: '';
    position: absolute;
    bottom: -30px; left: -30px;
    width: 100px; height: 100px;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.wph-preview-deco {
    position: absolute;
    pointer-events: none;
    user-select: none;
}
.wph-preview-deco.plane  { top: 14px; right: 18px; opacity: 0.75; }
.wph-preview-deco.star1  { top: 22px; left: 20px;  opacity: 0.55; }
.wph-preview-deco.star2  { bottom: 28px; right: 22px; opacity: 0.5; }
.wph-preview-deco.dot1   { top: 60px; left: 14px;  width:6px; height:6px; background:rgba(255,255,255,0.6); border-radius:50%; }
.wph-preview-deco.dot2   { bottom: 55px; left: 40px; width:4px; height:4px; background:rgba(255,255,255,0.5); border-radius:50%; }
.wph-preview-deco.dot3   { top: 45px; right: 55px;  width:5px; height:5px; background:rgba(255,255,255,0.55); border-radius:50%; }
#wph-admin-preview-container .whp-contact-content.whp-v2-panel {
    position: relative !important;
    bottom: auto !important;
    left: auto !important;
    right: auto !important;
    width: 100% !important;
    max-width: 320px !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06), 0 0 0 1px #e2e8f0 !important;
    opacity: 1 !important;
    visibility: visible !important;
    transform: none !important;
    display: block !important;
    border-radius: 20px !important;
    border: 1px solid #e2e8f0 !important;
}

/* Fade Panels */
.mb-contact-layout-wrapper {
    transition: opacity 0.3s;
}
.mb-contact-layout-wrapper.mb-disabled {
    opacity: 0.4;
    pointer-events: none;
    user-select: none;
}
.whp-v2-call-btn {
    padding: 15px 14px !important;
    margin-top: 15px !important;
    margin-bottom: 15px !important;
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

<form method="post" id="mb-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <!-- Left: title + desc + toggle -->
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M5 8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H9l-4 3V8z" fill="#fff"/></svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Kênh liên hệ', 'whp'); ?></h1>
            </div>
            <p class="mb-wph-page-subtitle">
                <?php echo wp_kses_post(__($itemInfo['desc'] ?? 'Tính năng cho phép cài đặt các popup trên trang web của bạn để khách hàng có thể tương tác trực tiếp hỏi về sản phẩm.', 'whp')); ?>
            </p>
            <div class="mb-wph-header-toggle-inline">
                <label class="mb-wph-switch">
                    <input type="checkbox" id="enable_contact_header" value="1" <?php echo $whp_contact_active == '1' ? 'checked' : ''; ?>>
                    <span class="mb-wph-slider"></span>
                </label>
                <span class="mb-wph-toggle-status <?php echo $whp_contact_active == '1' ? 'active' : ''; ?>" id="contact_header_status_text" style="font-size:13px;font-weight:600;">
                    <?php echo $whp_contact_active == '1' ? esc_html__('Đang bật', 'whp') : esc_html__('Đang tắt', 'whp'); ?>
                </span>
            </div>
        </div>
        <!-- Right: illustration background -->
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="ch_hbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f0f4ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#edf2ff" stop-opacity="0.5"/>
                        <stop offset="100%" stop-color="#e0eaff" stop-opacity="1"/>
                    </linearGradient>
                    <linearGradient id="ch_ib1" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#818cf8"/>
                        <stop offset="100%" stop-color="#4f46e5"/>
                    </linearGradient>
                    <linearGradient id="ch_ib2" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#6366f1"/>
                        <stop offset="100%" stop-color="#3730a3"/>
                    </linearGradient>
                    <linearGradient id="ch_ig" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#34d399"/>
                        <stop offset="100%" stop-color="#059669"/>
                    </linearGradient>
                    <linearGradient id="ls" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#eff6ff"/>
                        <stop offset="100%" stop-color="#dbeafe"/>
                    </linearGradient>
                    <linearGradient id="ch_lside" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#bfdbfe"/>
                        <stop offset="100%" stop-color="#93c5fd"/>
                    </linearGradient>
                    <filter id="shadow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="rgba(79,70,229,0.18)"/>
                    </filter>
                    <filter id="ch_shadowSm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="rgba(79,70,229,0.15)"/>
                    </filter>
                    <filter id="ch_shadowLaptop" x="-10%" y="-10%" width="120%" height="130%">
                        <feDropShadow dx="2" dy="8" stdDeviation="10" flood-color="rgba(99,102,241,0.2)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#ch_hbg)"/>

                <!-- ── Paper plane top-right ── -->
                <g transform="translate(630,14) rotate(-18)" filter="url(#ch_shadowSm)">
                    <path d="M0 9 L32 0 L10 22 Z" fill="#60a5fa"/>
                    <path d="M10 22 L8 14 L32 0 Z" fill="#93c5fd"/>
                </g>

                <!-- ── Deco dots ── -->
                <circle cx="546" cy="14" r="5"   fill="rgba(99,102,241,0.28)"/>
                <circle cx="562" cy="7"  r="3.5" fill="rgba(139,92,246,0.22)"/>
                <circle cx="533" cy="22" r="3"   fill="rgba(99,102,241,0.18)"/>

                <!-- ═══ LAPTOP (3D perspective) ═══ -->
                <!-- Keyboard base (viewed from above) -->
                <path d="M368 145 L598 145 L592 162 L374 162 Z" fill="url(#ch_lside)" filter="url(#ch_shadowLaptop)"/>
                <rect x="456" y="158" width="50" height="6" rx="3" fill="#93c5fd"/>
                <!-- Screen outer frame (slight tilt) -->
                <rect x="378" y="22" width="206" height="126" rx="12" fill="white" filter="url(#ch_shadowLaptop)"/>
                <rect x="378" y="22" width="206" height="126" rx="12" stroke="#c7d8f2" stroke-width="1.5" fill="none"/>
                <!-- Screen bezel -->
                <rect x="384" y="28" width="194" height="114" rx="8" fill="url(#ch_ls)"/>
                <!-- Screen glass (chat UI) -->
                <rect x="389" y="33" width="184" height="104" rx="6" fill="white"/>
                <!-- Chat header blue -->
                <rect x="389" y="33" width="184" height="26" rx="6" fill="#4f46e5"/>
                <circle cx="401" cy="46" r="9" fill="rgba(255,255,255,0.22)"/>
                <rect x="416" y="41" width="64" height="5.5" rx="2.75" fill="rgba(255,255,255,0.7)"/>
                <rect x="416" y="50" width="44" height="4" rx="2" fill="rgba(255,255,255,0.4)"/>
                <!-- Chat bubbles on screen -->
                <rect x="394" y="66" width="84" height="12" rx="6" fill="#4f46e5" opacity="0.9"/>
                <rect x="394" y="83" width="110" height="12" rx="6" fill="#f1f5f9"/>
                <rect x="427" y="100" width="86" height="12" rx="6" fill="#34d399" opacity="0.85"/>
                <rect x="394" y="117" width="70" height="12" rx="6" fill="#f1f5f9"/>
                <!-- Small chat icon on screen right -->
                <rect x="543" y="85" width="22" height="22" rx="6" fill="#4f46e5" opacity="0.15"/>
                <path d="M547 93 a2 2 0 0 1 2-2 h6 a2 2 0 0 1 2 2 v3 a2 2 0 0 1-2 2 h-2 l-2 2 v-2 h-2 a2 2 0 0 1-2-2 Z" fill="#4f46e5" opacity="0.6"/>

                <!-- ── Dotted connection lines ── -->
                <line x1="305" y1="86" x2="352" y2="86" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.8"/>
                <line x1="352" y1="86" x2="384" y2="55" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.7"/>
                <line x1="305" y1="86" x2="258" y2="52" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.7"/>
                <line x1="258" y1="52" x2="208" y2="36" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.6"/>
                <line x1="305" y1="86" x2="238" y2="116" stroke="#a5b4fc" stroke-width="2" stroke-dasharray="5,5" opacity="0.6"/>

                <!-- ── Headset icon — center white card ── -->
                <circle cx="305" cy="86" r="42" fill="white" filter="url(#shadow)"/>
                <circle cx="305" cy="86" r="34" fill="#f0f4ff"/>
                <!-- Headset arc -->
                <path d="M288 80 Q289 64 305 62 Q321 64 322 80" stroke="#4f46e5" stroke-width="4" fill="none" stroke-linecap="round"/>
                <!-- Ear cups -->
                <rect x="282" y="78" width="10" height="16" rx="5" fill="#4f46e5"/>
                <rect x="313" y="78" width="10" height="16" rx="5" fill="#4f46e5"/>
                <!-- Mic arm -->
                <path d="M283 93 Q279 102 281 107" stroke="#4f46e5" stroke-width="3" fill="none" stroke-linecap="round"/>
                <circle cx="281" cy="108" r="5" fill="#60a5fa"/>
                <circle cx="281" cy="108" r="3" fill="#4f46e5"/>

                <!-- ── Big floating icon 1: robot mascot (top center) ── -->
                <rect x="226" y="16" width="52" height="52" rx="14" fill="url(#ch_ib1)" filter="url(#shadow)"/>
                <circle cx="252" cy="34" r="14" fill="rgba(255,255,255,0.18)"/>
                <circle cx="246" cy="32" r="3.5" fill="white"/>
                <circle cx="258" cy="32" r="3.5" fill="white"/>
                <path d="M246 40 Q252 45 258 40" stroke="white" stroke-width="2" fill="none" stroke-linecap="round"/>
                <rect x="244" y="52" width="16" height="10" rx="5" fill="rgba(255,255,255,0.25)"/>
                <rect x="240" y="52" width="5" height="5" rx="2.5" fill="rgba(255,255,255,0.4)"/>
                <rect x="259" y="52" width="5" height="5" rx="2.5" fill="rgba(255,255,255,0.4)"/>

                <!-- ── Floating icon 2: shield check (top right) ── -->
                <rect x="340" y="6" width="44" height="44" rx="12" fill="url(#ch_ib2)" filter="url(#ch_shadowSm)"/>
                <path d="M362 16 L374 20 L374 31 Q368 38 362 40 Q356 38 350 31 L350 20 Z" fill="rgba(255,255,255,0.22)"/>
                <path d="M355 29 L360 34 L370 22" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>

                <!-- ── Floating icon 3: green shield (bottom-left) ── -->
                <rect x="196" y="106" width="44" height="44" rx="12" fill="url(#ch_ig)" filter="url(#ch_shadowSm)"/>
                <path d="M218 116 L228 120 L228 131 Q222 137 218 139 Q214 137 208 131 L208 120 Z" fill="rgba(255,255,255,0.22)"/>
                <path d="M212 128 L216 132 L224 123" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>

                <!-- ── Small node (left) ── -->
                <circle cx="168" cy="86" r="16" fill="white" filter="url(#ch_shadowSm)"/>
                <circle cx="168" cy="86" r="11" fill="#eef2ff"/>
                <circle cx="168" cy="86" r="6"  fill="#a5b4fc"/>

                <!-- ── Plant (bottom-right) ── -->
                <rect x="633" y="148" width="7" height="18" rx="3.5" fill="#4ade80" opacity="0.9"/>
                <ellipse cx="628" cy="144" rx="14" ry="8"  fill="#22c55e" opacity="0.95" transform="rotate(-30 628 144)"/>
                <ellipse cx="644" cy="141" rx="12" ry="7"  fill="#4ade80" opacity="0.85" transform="rotate(22 644 141)"/>
                <ellipse cx="636" cy="134" rx="9"  ry="5.5" fill="#86efac" opacity="0.9" transform="rotate(-8 636 134)"/>
                <rect x="630" y="154" width="7" height="14" rx="3.5" fill="#15803d" opacity="0.55"/>
            </svg>
        </div>
    </div>

    <!-- Feature Toggle Bar -->
    <div class="mb-wph-feature-bar">
        <div class="mb-wph-feature-bar-left">
            <div class="icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 8a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H9l-4 3V8z"/></svg>
            </div>
            <div class="info">
                <strong><?php esc_html_e('Kênh liên hệ', 'whp'); ?></strong>
                <span><?php esc_html_e('Bật tắt tính năng popup liên hệ trên website', 'whp'); ?></span>
            </div>
        </div>
        <div class="mb-wph-feature-bar-right">
            <span class="mb-wph-toggle-status <?php echo $whp_contact_active == '1' ? 'active' : ''; ?>" id="contact_toggle_status_text">
                <?php echo $whp_contact_active == '1' ? esc_html__('Đang bật', 'whp') : esc_html__('Đang tắt', 'whp'); ?>
            </span>
            <label class="mb-wph-switch">
                <input type="checkbox" id="enable_contact" name="whp_contact_active" value="1" <?php echo $whp_contact_active == '1' ? 'checked' : ''; ?>>
                <span class="mb-wph-slider"></span>
            </label>
        </div>
    </div>

    <!-- Notice: bật nhưng chưa điền thông tin -->
    <?php
    $has_contact_info = !empty($whp_contact_phone_data) ||
        (!empty($whp_contact_other_zalo) && ($whp_contact_other_zalo_active ?? '1') !== '0') ||
        (!empty($whp_contact_other_facebook) && ($whp_contact_other_facebook_active ?? '1') !== '0') ||
        (!empty($whp_contact_other_facebook_page) && ($whp_contact_other_messenger_active ?? '1') !== '0') ||
        (!empty($whp_contact_other_email) && ($whp_contact_other_email_active ?? '1') !== '0');
    $show_notice = ($whp_contact_active == '1') && !$has_contact_info;
    ?>
    <div id="mb-contact-empty-notice" style="display:<?php echo $show_notice ? 'flex' : 'none'; ?>;align-items:flex-start;gap:10px;background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:13px;color:#92400e;line-height:1.5;">
        <svg style="flex-shrink:0;margin-top:1px" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><strong><?php esc_html_e('Lưu ý:', 'whp'); ?></strong> <?php esc_html_e('Tính năng đang bật nhưng nếu chưa điền thông tin liên hệ (số điện thoại, Zalo, Messenger…), popup sẽ không hiển thị trên website.', 'whp'); ?></span>
    </div>

    <!-- Main Grid Content -->
    <div id="mb-contact-layout-wrapper" class="mb-contact-layout-wrapper<?php echo $whp_contact_active != '1' ? ' mb-disabled' : ''; ?>">

        <!-- Left Column: Settings -->
        <div class="mb-contact-settings-col">
            <div id="mb-settings-panels">

                <!-- Card 1: Lời chào -->
                <div class="mb-wph-card mb-wph-section-card accent-blue">
                    <div class="mb-wph-card-inner">
                        <div class="mb-wph-section-header">
                            <div class="mb-wph-section-header-left">
                                <div class="mb-wph-section-icon" style="background: #3858e9;">1</div>
                                <div class="mb-wph-section-header-text">
                                    <h3><?php esc_html_e('Lời chào', 'whp'); ?></h3>
                                    <p><?php esc_html_e('Tùy chỉnh nội dung hiển thị ở phần đầu của popup.', 'whp'); ?></p>
                                </div>
                            </div>
                            <?php
                            $is_off_init = ($whp_contact_online_status_text === 'Đang off');
                            $badge_bg_init = $is_off_init ? 'background: #fef2f2; color: #ef4444; border-color: #fca5a5;' : 'background: #eafaf1; color: #22c55e; border-color: #c2f0d5;';
                            $dot_bg_init = $is_off_init ? 'background: #ef4444;' : 'background: #22c55e;';
                            ?>
                            <div class="whp-v2-online-badge-admin" style="<?php echo $badge_bg_init; ?> padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; display: flex; align-items: center; gap: 6px; user-select: none; border: 1px solid;">
                                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; <?php echo $dot_bg_init; ?>"></span>
                                <span id="preview-online-status-badge"><?php echo esc_html($whp_contact_online_status_text ?: __('Đang online', 'whp')); ?></span>
                            </div>
                        </div>

                        <!-- Tiêu đề chính -->
                        <div class="mb-wph-field" style="position: relative;">
                            <label><?php esc_html_e('Tiêu đề chính', 'whp'); ?></label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <input type="text" name="whp_contact_phone_title" id="input_phone_title" maxlength="30" style="padding-right: 50px;"
                                    placeholder="<?php esc_attr_e('Hỗ trợ trực tuyến', 'whp'); ?>" value="<?php echo esc_attr($whp_contact_phone_title ?? ''); ?>">
                                <span style="position: absolute; right: 12px; font-size: 11px; color: #94a3b8; font-weight: 500; pointer-events: none;">
                                    <span id="len_phone_title"><?php echo mb_strlen($whp_contact_phone_title ?? '', 'UTF-8'); ?></span>/30
                                </span>
                            </div>
                        </div>

                        <!-- Mô tả ngắn -->
                        <div class="mb-wph-field" style="position: relative; margin-top: 14px;">
                            <label><?php esc_html_e('Mô tả ngắn', 'whp'); ?></label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <input type="text" name="whp_contact_design_greeting" id="input_design_greeting" maxlength="50" style="padding-right: 50px;"
                                    placeholder="<?php esc_attr_e('Chúng tôi luôn sẵn sàng hỗ trợ bạn', 'whp'); ?>" value="<?php echo esc_attr($whp_contact_design_greeting ?? ''); ?>">
                                <span style="position: absolute; right: 12px; font-size: 11px; color: #94a3b8; font-weight: 500; pointer-events: none;">
                                    <span id="len_design_greeting"><?php echo mb_strlen($whp_contact_design_greeting ?? '', 'UTF-8'); ?></span>/50
                                </span>
                            </div>
                        </div>

                        <!-- Trạng thái online -->
                        <div class="mb-wph-field" style="margin-top: 14px;">
                            <label><?php esc_html_e('Trạng thái online', 'whp'); ?></label>
                            <?php
                                $is_offline = ($whp_contact_online_status_text === 'Đang off');
                                $dot_color  = $is_offline ? '#ef4444' : '#22c55e';
                                $cur_label  = $is_offline ? 'Đang off' : 'Đang online';
                            ?>
                            <div class="mb-wph-custom-dropdown" id="status-dropdown">
                                <input type="hidden" name="whp_contact_online_status_text" id="input_online_status_text" value="<?php echo esc_attr($cur_label); ?>">
                                <div class="mb-wph-custom-dropdown-trigger" id="status-trigger">
                                    <span class="mb-wph-status-dot" id="status-select-dot" style="background:<?php echo $dot_color; ?>;"></span>
                                    <span class="label" id="status-display-label"><?php echo esc_html($cur_label); ?></span>
                                    <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </div>
                                <div class="mb-wph-custom-dropdown-menu">
                                    <div class="mb-wph-custom-dropdown-option<?php echo !$is_offline ? ' selected' : ''; ?>" data-value="Đang online" data-dot="#22c55e">
                                        <span class="mb-wph-status-dot" style="background:#22c55e;"></span> <?php esc_html_e('Đang online', 'whp'); ?>
                                    </div>
                                    <div class="mb-wph-custom-dropdown-option<?php echo $is_offline ? ' selected' : ''; ?>" data-value="Đang off" data-dot="#ef4444">
                                        <span class="mb-wph-status-dot" style="background:#ef4444;"></span> <?php esc_html_e('Đang off', 'whp'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card 2: Nút kích hoạt -->
                <div class="mb-wph-card mb-wph-section-card accent-orange">
                    <div class="mb-wph-card-inner">
                        <div class="mb-wph-section-header">
                            <div class="mb-wph-section-header-left">
                                <div class="mb-wph-section-icon" style="background: #f97316;">2</div>
                                <div class="mb-wph-section-header-text">
                                    <h3><?php esc_html_e('Nút kích hoạt', 'whp'); ?></h3>
                                    <p><?php esc_html_e('Tùy chỉnh nút nổi hiển thị trên website.', 'whp'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-field-row">
                            <!-- Chọn màu nút -->
                            <div class="mb-wph-field">
                                <label><?php esc_html_e('Chọn màu nút', 'whp'); ?></label>
                                <div class="mb-color-input-wrapper">
                                    <span class="mb-color-indicator" style="background: <?php echo esc_attr($whp_contact_design_color ?: '#00c217'); ?>;">
                                        <input type="color" id="mb-color-picker" value="<?php echo esc_attr($whp_contact_design_color ?: '#00c217'); ?>">
                                    </span>
                                    <input type="text" id="mb-color-hex" name="whp_contact_design_color" value="<?php echo esc_attr($whp_contact_design_color ?: '#00c217'); ?>">
                                </div>
                            </div>

                            <!-- Vị trí hiển thị -->
                            <div class="mb-wph-field">
                                <label><?php esc_html_e('Vị trí hiển thị', 'whp'); ?></label>
                                <div class="mb-wph-segmented-control">
                                    <?php
                                    $pos_is_right = in_array($whp_contact_design_position_x ?? '', ['right', 'mbwp-ct-right']);
                                    ?>
                                    <label class="mb-wph-segment <?php echo !$pos_is_right ? 'active' : ''; ?>" data-val="left" id="seg-left">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                                        <?php esc_html_e('Bên trái', 'whp'); ?>
                                    </label>
                                    <label class="mb-wph-segment <?php echo $pos_is_right ? 'active' : ''; ?>" data-val="right" id="seg-right">
                                        <?php esc_html_e('Bên phải', 'whp'); ?>
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </label>
                                    <input type="hidden" name="whp_contact_design_position_x" id="mb-position-x" value="<?php echo esc_attr($pos_is_right ? 'right' : 'left'); ?>">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="whp_contact_design_position_y" id="mb-position-y" value="<?php echo esc_attr(is_numeric($whp_contact_design_position_y) ? $whp_contact_design_position_y : '5'); ?>">

                    </div>
                </div>

                <!-- Card 3: Hotline hỗ trợ -->
                <div class="mb-wph-card mb-wph-section-card accent-green">
                    <div class="mb-wph-card-inner">
                        <div class="mb-wph-section-header">
                            <div class="mb-wph-section-header-left">
                                <div class="mb-wph-section-icon" style="background: #00c217;">3</div>
                                <div class="mb-wph-section-header-text">
                                    <h3><?php esc_html_e('Hotline hỗ trợ', 'whp'); ?></h3>
                                    <p><?php esc_html_e('Hiển thị nút gọi điện và đội ngũ tư vấn giúp khách hàng liên hệ nhanh chóng.', 'whp'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Tiêu đề nút hotline -->
                        <div class="mb-wph-field" style="position: relative;">
                            <label><?php esc_html_e('Tiêu đề nút hotline', 'whp'); ?></label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <input type="text" name="whp_contact_phone_btn_text" id="input_phone_btn_text" maxlength="20" style="padding-right: 50px;"
                                    placeholder="<?php echo esc_attr__('GỌI HOTLINE', 'whp'); ?>" value="<?php echo esc_attr($whp_contact_phone_btn_text ?: __('GỌI HOTLINE', 'whp')); ?>">
                                <span style="position: absolute; right: 12px; font-size: 11px; color: #94a3b8; font-weight: 500; pointer-events: none;">
                                    <span id="len_phone_btn_text"><?php echo mb_strlen($whp_contact_phone_btn_text ?: __('GỌI HOTLINE', 'whp'), 'UTF-8'); ?></span>/20
                                </span>
                            </div>
                        </div>

                        <!-- Ghi chú dưới nút Hotline -->
                        <div class="mb-wph-field" style="position: relative; margin-top: 14px;">
                            <label><?php esc_html_e('Ghi chú dưới nút Gọi Hotline (khi Online)', 'whp'); ?></label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <input type="text" name="whp_contact_phone_cta_note" id="input_phone_cta_note" maxlength="30" style="padding-right: 50px;"
                                    placeholder="<?php echo esc_attr__('Hỗ trợ nhanh chóng 24/7', 'whp'); ?>" value="<?php echo esc_attr($whp_contact_phone_cta_note ?: __('Hỗ trợ nhanh chóng 24/7', 'whp')); ?>">
                                <span style="position: absolute; right: 12px; font-size: 11px; color: #94a3b8; font-weight: 500; pointer-events: none;">
                                    <span id="len_phone_cta_note"><?php echo mb_strlen($whp_contact_phone_cta_note ?: __('Hỗ trợ nhanh chóng 24/7', 'whp'), 'UTF-8'); ?></span>/30
                                </span>
                            </div>
                        </div>

                        <div class="mb-wph-field" style="position: relative; margin-top: 14px;">
                            <label><?php esc_html_e('Ghi chú dưới nút Gọi Hotline (khi Offline)', 'whp'); ?></label>
                            <div style="position: relative; display: flex; align-items: center;">
                                <input type="text" name="whp_contact_phone_cta_note_offline" id="input_phone_cta_note_offline" maxlength="30" style="padding-right: 50px;"
                                    placeholder="<?php echo esc_attr__('Hiện tại ngoại tuyến', 'whp'); ?>" value="<?php echo esc_attr($whp_contact_phone_cta_note_offline ?: __('Hiện tại ngoại tuyến', 'whp')); ?>">
                                <span style="position: absolute; right: 12px; font-size: 11px; color: #94a3b8; font-weight: 500; pointer-events: none;">
                                    <span id="len_phone_cta_note_offline"><?php echo mb_strlen($whp_contact_phone_cta_note_offline ?: __('Hiện tại ngoại tuyến', 'whp'), 'UTF-8'); ?></span>/30
                                </span>
                            </div>
                        </div>

                        <!-- Danh sách nhân viên -->
                        <div class="mb-wph-field" style="margin-top: 14px;">
                            <label style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                <span><?php esc_html_e('Danh sách nhân viên', 'whp'); ?></span>
                                <span style="font-size: 11px; font-weight: 500; color: #ef4444; background: #fef2f2; padding: 2px 8px; border-radius: 4px; border: 1px solid #fca5a5; text-transform: none; letter-spacing: 0;">
                                    <?php esc_html_e('* Chỉ hỗ trợ tối đa 3 nhân viên', 'whp'); ?>
                                </span>
                            </label>
                            <div class="agent-list-wrapper" id="agent-list-container">
                                <?php
                                $agentCount = 0;
                                if (!empty($whp_contact_phone_data) && is_array($whp_contact_phone_data)) :
                                    foreach ($whp_contact_phone_data as $key => $phoneDataItem) :
                                        $agentCount++;
                                        $avatar = $phoneDataItem['avatar'] ?? 'contact-avata-women';
                                        $title  = $phoneDataItem['title'] ?? '';
                                        $phone  = $phoneDataItem['phone'] ?? '';
                                        
                                        $avatar_img = MB_WHP_URL . 'assets/admin/images/nu.svg';
                                        if ($avatar == 'contact-avata-men') {
                                            $avatar_img = MB_WHP_URL . 'assets/admin/images/nam.svg';
                                        } elseif ($avatar == 'contact-avata-support') {
                                            $avatar_img = MB_WHP_URL . 'assets/admin/images/24.svg';
                                        }
                                ?>
                                    <div class="agent-list-row" data-id="<?php echo esc_attr($agentCount); ?>">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                                <img src="<?php echo esc_url($avatar_img); ?>" style="width: 28px; height: 28px; object-fit: contain;" class="display-avatar-img">
                                            </div>
                                            <div>
                                                <strong class="display-name" style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a;"><?php echo esc_html($title); ?></strong>
                                                <span class="display-phone" style="display: block; font-size: 12.5px; color: #64748b; margin-top: 2px;"><?php echo esc_html($phone); ?></span>
                                            </div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="color: #22c55e; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px; user-select: none;">
                                                <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                                                Online
                                            </span>
                                            <button type="button" class="agent-btn-action btn-edit-agent"><i class="fas fa-pencil-alt" style="font-size: 12px;"></i></button>
                                            <button type="button" class="agent-btn-action btn-delete-agent"><i class="fas fa-trash-alt" style="font-size: 12px;"></i></button>
                                        </div>
                                        
                                        <!-- Hidden fields -->
                                        <input type="hidden" name="whp_contact_phone_data[<?php echo $agentCount; ?>][avatar]" class="input-avatar" value="<?php echo esc_attr($avatar); ?>">
                                        <input type="hidden" name="whp_contact_phone_data[<?php echo $agentCount; ?>][title]" class="input-title" value="<?php echo esc_attr($title); ?>">
                                        <input type="hidden" name="whp_contact_phone_data[<?php echo $agentCount; ?>][phone]" class="input-phone" value="<?php echo esc_attr($phone); ?>">
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>

                            <button type="button" id="btnAddMorePhone" data-number="<?php echo esc_attr($agentCount); ?>">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:4px;"><path d="M12 5v14M5 12h14"/></svg>
                                <?php esc_html_e('Thêm nhân viên', 'whp'); ?>
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Card 4: Kênh liên hệ khác -->
                <div class="mb-wph-card mb-wph-section-card accent-purple">
                    <div class="mb-wph-card-inner">
                        <div class="mb-wph-section-header">
                            <div class="mb-wph-section-header-left">
                                <div class="mb-wph-section-icon" style="background: #8b5cf6;">4</div>
                                <div class="mb-wph-section-header-text">
                                    <h3><?php esc_html_e('Kênh liên hệ khác', 'whp'); ?></h3>
                                    <p><?php esc_html_e('Bật/tắt và tùy chỉnh các kênh liên hệ hiển thị trong popup.', 'whp'); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Zalo -->
                        <div class="channel-list-row" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 10px; width: 120px; flex-shrink: 0;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #e0f2fe;">
                                    <img src="<?php echo esc_url(MB_WHP_URL . 'assets/frontend/images/zalo.png'); ?>" style="width: 24px; height: 24px; object-fit: contain;">
                                </div>
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Zalo</span>
                            </div>
                            <div style="width: 60px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <label class="mb-wph-switch mini">
                                    <input type="checkbox" name="whp_contact_other_zalo_active" class="channel-toggle-input" data-target="zalo" value="1" <?php echo ($whp_contact_other_zalo_active !== '0') ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider" style="border-radius: 12px;"></span>
                                </label>
                            </div>
                            <div class="wph-input-group" style="flex: 1; height: 38px;">
                                <span class="wph-input-addon"><?php esc_html_e('Nhập số Zalo', 'whp'); ?></span>
                                <input type="text" name="whp_contact_other_zalo" id="input_other_zalo" placeholder="0901234567" value="<?php echo esc_attr($whp_contact_other_zalo ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Facebook -->
                        <div class="channel-list-row" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 10px; width: 120px; flex-shrink: 0;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #1877f2; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 15px;">
                                    <i class="fab fa-facebook-f"></i>
                                </div>
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Facebook</span>
                            </div>
                            <div style="width: 60px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <label class="mb-wph-switch mini">
                                    <input type="checkbox" name="whp_contact_other_facebook_active" class="channel-toggle-input" data-target="facebook" value="1" <?php echo ($whp_contact_other_facebook_active !== '0') ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider" style="border-radius: 12px;"></span>
                                </label>
                            </div>
                            <div class="wph-input-group" style="flex: 1; height: 38px;">
                                <span class="wph-input-addon"><?php esc_html_e('Nhập link Facebook', 'whp'); ?></span>
                                <input type="text" name="whp_contact_other_facebook" id="input_other_facebook" placeholder="https://facebook.com/page" value="<?php echo esc_attr($whp_contact_other_facebook ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Messenger -->
                        <div class="channel-list-row" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 10px; width: 120px; flex-shrink: 0;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #0084ff; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 15px;">
                                    <i class="fab fa-facebook-messenger"></i>
                                </div>
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Messenger</span>
                            </div>
                            <div style="width: 60px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <label class="mb-wph-switch mini">
                                    <input type="checkbox" name="whp_contact_other_messenger_active" class="channel-toggle-input" data-target="messenger" value="1" <?php echo ($whp_contact_other_messenger_active !== '0') ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider" style="border-radius: 12px;"></span>
                                </label>
                            </div>
                            <div class="wph-input-group" style="flex: 1; height: 38px;">
                                <span class="wph-input-addon"><?php esc_html_e('Nhập link Messenger', 'whp'); ?></span>
                                <input type="text" name="whp_contact_other_facebook_page" id="input_other_messenger" placeholder="https://m.me/pageid" value="<?php echo esc_attr($whp_contact_other_facebook_page ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="channel-list-row" style="display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 16px;">
                            <div style="display: flex; align-items: center; gap: 10px; width: 120px; flex-shrink: 0;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: #ef4444; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 15px;">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Email</span>
                            </div>
                            <div style="width: 60px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                <label class="mb-wph-switch mini">
                                    <input type="checkbox" name="whp_contact_other_email_active" class="channel-toggle-input" data-target="email" value="1" <?php echo ($whp_contact_other_email_active !== '0') ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider" style="border-radius: 12px;"></span>
                                </label>
                            </div>
                            <div class="wph-input-group" style="flex: 1; height: 38px;">
                                <span class="wph-input-addon"><?php esc_html_e('Nhập email', 'whp'); ?></span>
                                <input type="text" name="whp_contact_other_email" id="input_other_email" placeholder="support@example.com" value="<?php echo esc_attr($whp_contact_other_email ?? ''); ?>">
                            </div>
                        </div>

                        <!-- removed add channel button -->
                    </div>
                </div>

                <!-- Card 5: Cài đặt hiển thị -->
                <div class="mb-wph-card mb-wph-section-card accent-darkblue">
                    <div class="mb-wph-card-inner">
                        <div class="mb-wph-section-header">
                            <div class="mb-wph-section-header-left">
                                <div class="mb-wph-section-icon" style="background: #1d4ed8;">5</div>
                                <div class="mb-wph-section-header-text">
                                    <h3><?php esc_html_e('Cài đặt hiển thị', 'whp'); ?></h3>
                                    <p><?php esc_html_e('Tùy chỉnh các thiết lập hiển thị của popup.', 'whp'); ?></p>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0 32px;">
                            <!-- Row 1 -->
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="font-size: 13px; font-weight: 600; color: #475569; flex-shrink: 0; min-width: 165px;"><?php esc_html_e('Khoảng cách đẩy (px)', 'whp'); ?></span>
                                <input type="number" name="whp_contact_bottom_distance" id="input_bottom_distance" value="<?php echo esc_attr($whp_contact_bottom_distance !== '' ? $whp_contact_bottom_distance : '20'); ?>" style="flex: 1; padding: 8px 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13.5px; color: #1e293b; background: #fff; text-align: center; outline: none; transition: border-color 0.2s, box-shadow 0.2s; min-width: 0;">
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9;">
                                <span style="font-size: 13px; font-weight: 600; color: #475569;"><?php esc_html_e('Hiển thị trên Desktop', 'whp'); ?></span>
                                <label class="mb-wph-switch mini">
                                    <input type="checkbox" name="whp_contact_display_desktop" value="1" <?php echo ($whp_contact_display_desktop !== '0') ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider" style="border-radius: 12px;"></span>
                                </label>
                            </div>
                            <!-- Row 2 -->
                            <div style="display: flex; align-items: center; gap: 12px; padding: 12px 0;">
                                <span style="font-size: 13px; font-weight: 600; color: #475569; flex-shrink: 0; min-width: 165px;"><?php esc_html_e('Hiệu ứng mở popup', 'whp'); ?></span>
                                <?php
                                    $effect_val = $whp_contact_popup_effect ?: 'zoom-in';
                                    $effect_map = ['zoom-in'=>'Zoom In','fade-in'=>'Fade In','slide-up'=>'Slide Up'];
                                    $effect_display = $effect_map[$effect_val] ?? 'Zoom In';
                                ?>
                                <div class="mb-wph-custom-dropdown centered" id="effect-dropdown" style="flex: 1; min-width: 0;">
                                    <input type="hidden" name="whp_contact_popup_effect" id="input_popup_effect" value="<?php echo esc_attr($effect_val); ?>">
                                    <div class="mb-wph-custom-dropdown-trigger" id="effect-trigger">
                                        <span class="label" id="effect-display-label"><?php echo esc_html($effect_display); ?></span>
                                        <svg class="chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                    </div>
                                    <div class="mb-wph-custom-dropdown-menu">
                                        <div class="mb-wph-custom-dropdown-option<?php echo $effect_val==='zoom-in'?' selected':''; ?>" data-value="zoom-in"><span class="wph-option-dot"></span>Zoom In</div>
                                        <div class="mb-wph-custom-dropdown-option<?php echo $effect_val==='fade-in'?' selected':''; ?>" data-value="fade-in"><span class="wph-option-dot"></span>Fade In</div>
                                        <div class="mb-wph-custom-dropdown-option<?php echo $effect_val==='slide-up'?' selected':''; ?>" data-value="slide-up"><span class="wph-option-dot"></span>Slide Up</div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0;">
                                <span style="font-size: 13px; font-weight: 600; color: #475569;"><?php esc_html_e('Hiển thị trên Mobile', 'whp'); ?></span>
                                <label class="mb-wph-switch mini">
                                    <input type="checkbox" name="whp_contact_display_mobile" value="1" <?php echo ($whp_contact_display_mobile !== '0') ? 'checked' : ''; ?>>
                                    <span class="mb-wph-slider" style="border-radius: 12px;"></span>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>

            </div><!-- /#mb-settings-panels -->
        </div><!-- /.mb-contact-settings-col -->

        <!-- Right Column: Live Preview -->
        <div class="mb-contact-preview-col" style="position: sticky; top: 32px;">
            <div class="mb-wph-card">
                <div class="mb-wph-card-inner" style="padding: 24px;">
                    <h3 style="margin: 0 0 4px 0; font-size: 15px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Xem trước popup', 'whp'); ?></h3>
                    <p style="margin: 0 0 20px 0; font-size: 13px; color: #64748b;"><?php esc_html_e('Đây là giao diện popup hiển thị trên website', 'whp'); ?></p>
                    
                    <div id="wph-admin-preview-container">
                        <!-- Decorative elements -->
                        <span class="wph-preview-deco plane">
                            <svg width="36" height="36" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M58 6L6 26l20 8 8 20 24-48z" fill="white" fill-opacity="0.9"/>
                                <path d="M26 34l8 20 5-12" stroke="rgba(120,80,200,0.4)" stroke-width="1.5" fill="none"/>
                            </svg>
                        </span>
                        <span class="wph-preview-deco star1">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z" fill="white" fill-opacity="0.8"/></svg>
                        </span>
                        <span class="wph-preview-deco star2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z" fill="white" fill-opacity="0.7"/></svg>
                        </span>
                        <span class="wph-preview-deco dot1"></span>
                        <span class="wph-preview-deco dot2"></span>
                        <span class="wph-preview-deco dot3"></span>
                        <!-- Panel V2 -->
                        <div class="whp-contact-content whp-v2-panel" id="whp-admin-panel">
                            <!-- Header -->
                            <div class="whp-v2-header" id="preview-header" style="background:<?php echo esc_attr($whp_contact_design_color ?: '#00c217'); ?>;">
                                <div class="whp-v2-header-left">
                                    <div class="whp-v2-header-icon" id="preview-header-icon" style="color:<?php echo esc_attr($whp_contact_design_color ?: '#00c217'); ?>;">
                                        <svg width="28" height="28" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M32 6C17.6 6 6 17.6 6 32c0 1 .8 1.8 1.8 1.8s1.8-.8 1.8-1.8C9.6 19.3 19.7 9.2 32 9.2s22.4 10.1 22.4 22.8c0 1 .8 1.8 1.8 1.8s1.8-.8 1.8-1.8C58 17.6 46.4 6 32 6Z" fill="currentColor"/>
                                            <path d="M32 12c-11.6 0-21 9.4-21 21c0 5 1.8 9.7 5 13.4l-1.9 6.2c-.3 1 .7 1.9 1.7 1.6l6.8-2.6c2.9 1.6 6.1 2.4 9.4 2.4c11.6 0 21-9.4 21-21S43.6 12 32 12Z" fill="currentColor"/>
                                            <circle cx="25" cy="32" r="3" fill="#fff"/><circle cx="39" cy="32" r="3" fill="#fff"/>
                                            <path d="M25 39c2.5 3.5 11.5 3.5 14 0" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                                            <rect x="4" y="27" width="5" height="10" rx="2.5" fill="currentColor"/>
                                            <rect x="55" y="27" width="5" height="10" rx="2.5" fill="currentColor"/>
                                            <path d="M55 35c0 6-8 10-12 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none"/>
                                        </svg>
                                    </div>
                                    <div class="whp-v2-header-text">
                                        <strong id="preview-header-title"><?php echo esc_html($whp_contact_phone_title ?: __('Hỗ trợ trực tuyến', 'whp')); ?></strong>
                                        <span id="preview-greeting"><?php echo esc_html($whp_contact_design_greeting ?: __('Chúng tôi luôn sẵn sàng hỗ trợ bạn', 'whp')); ?></span>
                                    </div>
                                </div>
                                <?php
                                $is_off_init = ($whp_contact_online_status_text === 'Đang off');
                                $preview_badge_style = $is_off_init ? 'background: rgba(239, 68, 68, 0.15); color: #ef4444;' : '';
                                $preview_dot_style = $is_off_init ? 'background: #ef4444;' : '';
                                ?>
                                <div class="whp-v2-online-badge" style="<?php echo $preview_badge_style; ?>">
                                    <span class="whp-v2-dot" style="<?php echo $preview_dot_style; ?>"></span>
                                    <span id="preview-online-badge-text"><?php echo esc_html($whp_contact_online_status_text ?: __('Đang online', 'whp')); ?></span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="whp-v2-body">
                                <div class="whp-v2-illustration-container">
                                    <img src="<?php echo esc_url(MB_WHP_URL . 'assets/frontend/images/support-team.png'); ?>" class="whp-v2-illustration" alt="Support Team">
                                </div>
                                <h3 class="whp-v2-welcome"><?php esc_html_e('Chào bạn, chúng tôi có thể giúp gì?', 'whp'); ?></h3>
                                <p class="whp-v2-sub"><?php esc_html_e('Chọn kênh liên hệ phù hợp để được hỗ trợ nhanh nhất', 'whp'); ?></p>

                                <!-- Hotline Button Container -->
                                <div id="preview-hotline-btn-container">
                                    <?php 
                                    $prev_color = esc_attr($whp_contact_design_color ?: '#00c217');
                                    $prev_btn_label = esc_html($whp_contact_phone_btn_text ?: __('GỌI HOTLINE', 'whp'));
                                    
                                    if (!empty($whp_contact_phone_data) && count((array)$whp_contact_phone_data) > 1) : ?>
                                        <button type="button" class="whp-v2-call-btn whp-toggle-phones-btn" id="preview-hotline-btn" style="background:<?php echo $prev_color; ?>;">
                                            <span class="whp-v2-call-icon" id="preview-call-icon" style="color:<?php echo $prev_color; ?>;"><i class="fas fa-phone-alt"></i></span>
                                            <span class="whp-v2-call-label"><?php echo $prev_btn_label; ?></span>
                                            <i class="fas fa-chevron-right whp-v2-chevron"></i>
                                        </button>
                                        <div class="whp-agents-collapse" id="preview-agents-collapse" style="display:none;">
                                            <div class="whp-agents-collapse-inner">
                                                <?php foreach ((array)$whp_contact_phone_data as $item) :
                                                    $av    = $item['avatar'] ?? 'contact-avata-women';
                                                    $title_agent = $item['title']  ?? '';
                                                    $phone_num   = $item['phone']  ?? '';
                                                    
                                                    $avatar_img = MB_WHP_URL . 'assets/admin/images/nu.svg';
                                                    if ($av == 'contact-avata-men') {
                                                        $avatar_img = MB_WHP_URL . 'assets/admin/images/nam.svg';
                                                    } elseif ($av == 'contact-avata-support') {
                                                        $avatar_img = MB_WHP_URL . 'assets/admin/images/24.svg';
                                                    }
                                                ?>
                                                    <a href="tel:<?php echo esc_attr($phone_num); ?>" class="whp-agent-row">
                                                        <img src="<?php echo esc_url($avatar_img); ?>" alt="Avatar">
                                                        <div class="whp-agent-info">
                                                            <strong><?php echo esc_html($title_agent); ?></strong>
                                                            <span><?php echo esc_html($phone_num); ?></span>
                                                        </div>
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php elseif (!empty($whp_contact_phone_data) && count((array)$whp_contact_phone_data) === 1) : 
                                        $first_agent = array_values((array)$whp_contact_phone_data)[0];
                                        $phone_num = $first_agent['phone'] ?? '';
                                    ?>
                                        <a href="tel:<?php echo esc_attr($phone_num); ?>" class="whp-v2-call-btn" id="preview-hotline-btn" style="background:<?php echo $prev_color; ?>;">
                                            <span class="whp-v2-call-icon" id="preview-call-icon" style="color:<?php echo $prev_color; ?>;"><i class="fas fa-phone-alt"></i></span>
                                            <span class="whp-v2-call-label"><?php echo $prev_btn_label; ?></span>
                                            <i class="fas fa-chevron-right whp-v2-chevron"></i>
                                        </a>
                                    <?php else : ?>
                                        <div class="whp-v2-call-btn" id="preview-hotline-btn" style="background:<?php echo $prev_color; ?>; opacity:.6; cursor:default;">
                                            <span class="whp-v2-call-icon" id="preview-call-icon" style="color:<?php echo $prev_color; ?>;"><i class="fas fa-phone-alt"></i></span>
                                            <span class="whp-v2-call-label"><?php echo $prev_btn_label; ?></span>
                                            <i class="fas fa-chevron-right whp-v2-chevron"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <p class="whp-v2-cta-note">
                                    <i class="fas fa-check-circle"></i> 
                                    <span id="preview-cta-note"><?php 
                                        if ($whp_contact_online_status_text === 'Đang off') {
                                            echo esc_html($whp_contact_phone_cta_note_offline ?: __('Hiện tại ngoại tuyến', 'whp'));
                                        } else {
                                            echo esc_html($whp_contact_phone_cta_note ?: __('Hỗ trợ nhanh chóng 24/7', 'whp'));
                                        }
                                    ?></span>
                                </p>
                                <?php
                                    $any_ch = (
                                        ($whp_contact_other_zalo_active !== '0' && $whp_contact_other_zalo) ||
                                        ($whp_contact_other_messenger_active !== '0' && $whp_contact_other_facebook_page) ||
                                        ($whp_contact_other_facebook_active !== '0' && $whp_contact_other_facebook) ||
                                        ($whp_contact_other_email_active !== '0' && $whp_contact_other_email)
                                    );
                                ?>
                                <div class="whp-v2-divider" id="preview-social-divider"<?php echo $any_ch ? '' : ' style="display:none;"'; ?>><span><?php esc_html_e('Hoặc liên hệ qua kênh', 'whp'); ?></span></div>

                                <div class="whp-v2-social-grid" id="preview-social-grid">
                                    <span class="whp-v2-social-card preview-zalo-card" style="<?php echo ($whp_contact_other_zalo_active !== '0' && $whp_contact_other_zalo) ? '' : 'display:none;'; ?>">
                                        <div class="whp-v2-social-icon zalo"><img src="<?php echo esc_url(MB_WHP_URL . 'assets/frontend/images/zalo.png'); ?>" alt="Zalo"></div>
                                        <strong>Zalo</strong><span><?php esc_html_e('Nhắn Zalo', 'whp'); ?></span>
                                    </span>
                                    <span class="whp-v2-social-card preview-messenger-card" style="<?php echo ($whp_contact_other_messenger_active !== '0' && $whp_contact_other_facebook_page) ? '' : 'display:none;'; ?>">
                                        <div class="whp-v2-social-icon messenger"><i class="fab fa-facebook-messenger"></i></div>
                                        <strong>Messenger</strong><span><?php esc_html_e('Chat ngay', 'whp'); ?></span>
                                    </span>
                                    <span class="whp-v2-social-card preview-facebook-card" style="<?php echo ($whp_contact_other_facebook_active !== '0' && $whp_contact_other_facebook) ? '' : 'display:none;'; ?>">
                                        <div class="whp-v2-social-icon facebook"><i class="fab fa-facebook-f"></i></div>
                                        <strong>Facebook</strong><span><?php esc_html_e('Ghé Fanpage', 'whp'); ?></span>
                                    </span>
                                    <span class="whp-v2-social-card preview-email-card" style="<?php echo ($whp_contact_other_email_active !== '0' && $whp_contact_other_email) ? '' : 'display:none;'; ?>">
                                        <div class="whp-v2-social-icon email"><i class="fas fa-envelope"></i></div>
                                        <strong>Email</strong><span><?php esc_html_e('Gửi thư', 'whp'); ?></span>
                                    </span>
                                </div>

                                <p class="whp-v2-footer-note" id="preview-footer-note" style="background:<?php $c=$whp_contact_design_color?:'#00c217'; $rgb=sscanf($c,'#%02x%02x%02x'); echo "rgba({$rgb[0]},{$rgb[1]},{$rgb[2]},0.08); border-color:rgba({$rgb[0]},{$rgb[1]},{$rgb[2]},0.3); color:{$c};"; ?>">
                                    <i class="fas fa-check-circle" id="preview-footer-icon" style="color:<?php echo esc_attr($whp_contact_design_color ?: '#00c217'); ?>;"></i>
                                    <span><?php esc_html_e('Đội ngũ của chúng tôi sẽ phản hồi bạn trong thời gian sớm nhất! 👋', 'whp'); ?></span>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <p style="margin: 16px 0 0; font-size: 12.5px; color: #94a3b8; line-height: 1.5; text-align: center;">
                        <?php esc_html_e('Lưu ý: Đây là bản xem trước, giao diện thực tế có thể khác nhau tùy theo giao diện website của bạn.', 'whp'); ?>
                    </p>
                </div>
            </div>

            <!-- Tips & Usage Guide Card -->
            <div class="mb-wph-card" style="margin-top: 16px;">
                <div class="mb-wph-card-inner" style="padding: 20px 22px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                        <div style="width: 32px; height: 32px; border-radius: 10px; background: linear-gradient(135deg, #f59e0b, #fbbf24); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 3px 8px rgba(245,158,11,0.25);">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        </div>
                        <div>
                            <h3 style="margin: 0 0 1px; font-size: 13.5px; font-weight: 700; color: #0f172a;"><?php esc_html_e('Hướng dẫn & Mẹo sử dụng', 'whp'); ?></h3>
                            <p style="margin: 0; font-size: 11.5px; color: #94a3b8;"><?php esc_html_e('Tối ưu popup liên hệ cho website của bạn', 'whp'); ?></p>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px;">

                        <div style="display: flex; gap: 10px; padding: 10px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; align-items: flex-start;">
                            <span style="width: 18px; height: 18px; border-radius: 50%; background: #22c55e; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                            <div>
                                <strong style="font-size: 12px; color: #166534; display: block; margin-bottom: 1px;"><?php esc_html_e('Thêm ít nhất 1 nhân viên hotline', 'whp'); ?></strong>
                                <span style="font-size: 11.5px; color: #16a34a; line-height: 1.4; display: block;"><?php esc_html_e('Nút "GỌI HOTLINE" chỉ hoạt động khi có số điện thoại trong mục Hotline hỗ trợ.', 'whp'); ?></span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; padding: 10px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; align-items: flex-start;">
                            <span style="width: 18px; height: 18px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                            </span>
                            <div>
                                <strong style="font-size: 12px; color: #1e3a8a; display: block; margin-bottom: 1px;"><?php esc_html_e('Bật kênh mạng xã hội phù hợp', 'whp'); ?></strong>
                                <span style="font-size: 11.5px; color: #2563eb; line-height: 1.4; display: block;"><?php esc_html_e('Chỉ bật kênh bạn thực sự hoạt động để tránh gây nhầm lẫn cho khách hàng.', 'whp'); ?></span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; padding: 10px 12px; background: #fdf4ff; border: 1px solid #e9d5ff; border-radius: 8px; align-items: flex-start;">
                            <span style="width: 18px; height: 18px; border-radius: 50%; background: #a855f7; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="5" fill="#fff"/></svg>
                            </span>
                            <div>
                                <strong style="font-size: 12px; color: #581c87; display: block; margin-bottom: 1px;"><?php esc_html_e('Đồng bộ màu nút với thương hiệu', 'whp'); ?></strong>
                                <span style="font-size: 11.5px; color: #7c3aed; line-height: 1.4; display: block;"><?php esc_html_e('Màu nút nổi và header popup khớp nhau. Dùng màu chủ đạo của website để popup trông tự nhiên.', 'whp'); ?></span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; padding: 10px 12px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; align-items: flex-start;">
                            <span style="width: 18px; height: 18px; border-radius: 50%; background: #f97316; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="#fff" stroke-width="2"/></svg>
                            </span>
                            <div>
                                <strong style="font-size: 12px; color: #7c2d12; display: block; margin-bottom: 1px;"><?php esc_html_e('Popup tự ẩn khi bảo trì', 'whp'); ?></strong>
                                <span style="font-size: 11.5px; color: #c2410c; line-height: 1.4; display: block;"><?php esc_html_e('Khi chế độ bảo trì đang bật, popup liên hệ tự động ẩn để tránh nhận yêu cầu hỗ trợ ngoài giờ.', 'whp'); ?></span>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; align-items: flex-start;">
                            <span style="width: 18px; height: 18px; border-radius: 50%; background: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#fff" stroke-width="2.5"/><path d="M8 21h8M12 17v4" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                            </span>
                            <div>
                                <strong style="font-size: 12px; color: #374151; display: block; margin-bottom: 1px;"><?php esc_html_e('Chọn vị trí tránh chồng widget', 'whp'); ?></strong>
                                <span style="font-size: 11.5px; color: #64748b; line-height: 1.4; display: block;"><?php esc_html_e('Nếu website có menu cố định góc phải, chọn "Bên trái" để tránh chồng chéo giao diện.', 'whp'); ?></span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div><!-- /.mb-contact-layout-wrapper -->

    <!-- Save Bar -->
    <div id="mb-contact-save-bar" class="mb-wph-save-bar">
        <span class="mb-wph-save-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            <?php esc_html_e('Các thay đổi sẽ áp dụng ngay sau khi lưu', 'whp'); ?>
        </span>
        <button type="submit" name="submit" class="mb-wph-save-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?php esc_html_e('Lưu thông tin', 'whp'); ?>
        </button>
    </div>

    <!-- Lưu ý bảo trì -->
    <?php $maintenance_on = whp_get_option('whp_maintenance_active'); ?>
    <?php if ($maintenance_on) : ?>
    <div style="display:flex;align-items:flex-start;gap:10px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-top:12px;">
        <span class="dashicons dashicons-warning" style="color:#d97706;font-size:18px;width:18px;height:18px;flex-shrink:0;margin-top:1px;"></span>
        <div>
            <strong style="font-size:13px;color:#92400e;"><?php esc_html_e('Chế độ bảo trì đang BẬT', 'whp'); ?></strong>
            <p style="font-size:12.5px;color:#78350f;margin:2px 0 0;line-height:1.5;"><?php esc_html_e('Kênh liên hệ sẽ không hiển thị khi website đang ở chế độ bảo trì.', 'whp'); ?>
                <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=maintenance'); ?>" style="color:#d97706;font-weight:600;">Tắt bảo trì →</a>
            </p>
        </div>
    </div>
    <?php endif; ?>

</div><!-- /.mb-wph-page -->
</form>

<!-- Modal popup for Adding/Editing employees -->
<div id="wph-agent-modal" class="wph-modal" style="display: none;">
    <div class="wph-modal-overlay"></div>
    <div class="wph-modal-content">
        <div class="wph-modal-header">
            <h4 id="modal-agent-title"><?php esc_html_e('Thêm nhân viên', 'whp'); ?></h4>
            <button type="button" class="wph-modal-close" id="modal-agent-close-btn">&times;</button>
        </div>
        <div class="wph-modal-body">
            <input type="hidden" id="modal-agent-row-id" value="">
            
            <!-- Avatar Selection -->
            <div class="mb-wph-field">
                <label><?php esc_html_e('Hình đại diện', 'whp'); ?></label>
                <div class="form-avatar-group">
                    <div class="form-avatar-item">
                        <label for="modal_avatar_nu">
                            <img src="<?php echo esc_url(MB_WHP_URL . 'assets/admin/images/nu.svg'); ?>">
                        </label>
                        <input type="radio" name="modal_avatar" value="contact-avata-women" id="modal_avatar_nu" checked>
                        <span><?php esc_html_e('Nữ', 'whp'); ?></span>
                    </div>
                    <div class="form-avatar-item">
                        <label for="modal_avatar_nam">
                            <img src="<?php echo esc_url(MB_WHP_URL . 'assets/admin/images/nam.svg'); ?>">
                        </label>
                        <input type="radio" name="modal_avatar" value="contact-avata-men" id="modal_avatar_nam">
                        <span><?php esc_html_e('Nam', 'whp'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Tên hiển thị -->
            <div class="mb-wph-field" style="margin-top: 16px;">
                <label><?php esc_html_e('Tên hiển thị', 'whp'); ?></label>
                <input type="text" id="modal-agent-name" placeholder="<?php esc_attr_e('Ví dụ: Nguyễn Văn A', 'whp'); ?>" value="">
            </div>
            
            <!-- Số điện thoại -->
            <div class="mb-wph-field" style="margin-top: 14px;">
                <label><?php esc_html_e('Số điện thoại', 'whp'); ?></label>
                <input type="text" id="modal-agent-phone" placeholder="<?php esc_attr_e('Ví dụ: 0901234567', 'whp'); ?>" value="">
            </div>
        </div>
        <div class="wph-modal-footer">
            <button type="button" class="wph-btn wph-btn-secondary" id="modal-agent-cancel"><?php esc_html_e('Hủy', 'whp'); ?></button>
            <button type="button" class="wph-btn wph-btn-primary" id="modal-agent-save"><?php esc_html_e('Lưu', 'whp'); ?></button>
        </div>
    </div>
</div>

<script>
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
(function($) {
    'use strict';

    // Globals needed for dynamic preview images
    const MB_WHP_URL_ASSETS = '<?php echo esc_js(MB_WHP_URL); ?>';

    // i18n strings — avoid hardcoded Vietnamese in JS
    var whpContactI18n = {
        enabled:          '<?php echo esc_js(__('Đang bật', 'whp')); ?>',
        disabled:         '<?php echo esc_js(__('Đang tắt', 'whp')); ?>',
        online:           '<?php echo esc_js(__('Đang online', 'whp')); ?>',
        offline:          '<?php echo esc_js(__('Đang off', 'whp')); ?>',
        hotlineBtnDefault:'<?php echo esc_js(__('GỌI HOTLINE', 'whp')); ?>'
    };

    // Toggle bật/tắt panels — feature bar toggle là nguồn truth (có name→submit)
    var toggleInput      = document.getElementById('enable_contact');       // feature bar (submits)
    var toggleHeader     = document.getElementById('enable_contact_header'); // header card (visual only)
    var layoutWrapper    = document.getElementById('mb-contact-layout-wrapper');
    var statusText       = document.getElementById('contact_toggle_status_text');
    var headerStatusText = document.getElementById('contact_header_status_text');
    var emptyNotice      = document.getElementById('mb-contact-empty-notice');
    var saveBar          = document.getElementById('mb-contact-save-bar');
    var mbForm           = document.getElementById('mb-form');

    function hasContactInfo() {
        var phoneInputs = document.querySelectorAll('.input-phone');
        for (var i = 0; i < phoneInputs.length; i++) {
            if (phoneInputs[i].value.trim()) return true;
        }
        var channels = [
            { field: 'input_other_zalo',     toggle: 'whp_contact_other_zalo_active' },
            { field: 'input_other_facebook',  toggle: 'whp_contact_other_facebook_active' },
            { field: 'input_other_messenger', toggle: 'whp_contact_other_messenger_active' },
            { field: 'input_other_email',     toggle: 'whp_contact_other_email_active' },
        ];
        for (var j = 0; j < channels.length; j++) {
            var el  = document.getElementById(channels[j].field);
            var tog = document.querySelector('[name="' + channels[j].toggle + '"]');
            if (el && el.value.trim() && tog && tog.checked) return true;
        }
        return false;
    }

    function updateNoticeVisibility(toggleChecked) {
        if (!emptyNotice) return;
        emptyNotice.style.display = (toggleChecked && !hasContactInfo()) ? 'flex' : 'none';
    }

    function updateToggleUI(checked, doSave) {
        if (layoutWrapper) {
            if (checked) layoutWrapper.classList.remove('mb-disabled');
            else         layoutWrapper.classList.add('mb-disabled');
        }
        if (saveBar) saveBar.style.display = checked ? '' : 'none';
        if (statusText)       { statusText.textContent = checked ? whpContactI18n.enabled : whpContactI18n.disabled; statusText.classList.toggle('active', checked); }
        if (headerStatusText) { headerStatusText.textContent = checked ? whpContactI18n.enabled : whpContactI18n.disabled; headerStatusText.classList.toggle('active', checked); }
        if (toggleHeader) toggleHeader.checked = checked;
        updateNoticeVisibility(checked);
        if (doSave) {
            var _nonce = '<?php echo esc_js( wp_create_nonce('whp_contact_toggle') ); ?>';
            var _fd = new FormData();
            _fd.append('action', 'whp_contact_toggle_active');
            _fd.append('nonce', _nonce);
            _fd.append('active', checked ? '1' : '0');
            fetch(ajaxurl, { method: 'POST', body: _fd })
                .then(function(r){ return r.json(); })
                .then(function(r){
                    if (r.success) {
                        whpToast(checked ? '<?php echo esc_js(__('Đã bật Kênh liên hệ', 'whp')); ?>' : '<?php echo esc_js(__('Đã tắt Kênh liên hệ', 'whp')); ?>', 'success');
                    } else {
                        whpToast('<?php echo esc_js(__('Lỗi lưu trạng thái', 'whp')); ?>', 'error');
                    }
                })
                .catch(function(){ whpToast('<?php echo esc_js(__('Lỗi kết nối', 'whp')); ?>', 'error'); });
        }
    }

    if (toggleInput) {
        toggleInput.addEventListener('change', function() { updateToggleUI(this.checked, true); });
        updateToggleUI(toggleInput.checked, false);
    }
    // Header toggle syncs back to feature bar (no independent submit)
    if (toggleHeader) {
        toggleHeader.addEventListener('change', function() {
            if (toggleInput) { toggleInput.checked = this.checked; updateToggleUI(this.checked, true); }
        });
    }

    // Auto-update notice when user fills in contact info or toggles channels
    ['input_other_zalo','input_other_facebook','input_other_messenger','input_other_email'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', function() { if (toggleInput) updateNoticeVisibility(toggleInput.checked); });
    });
    document.querySelectorAll('.channel-toggle-input').forEach(function(tog) {
        tog.addEventListener('change', function() { if (toggleInput) updateNoticeVisibility(toggleInput.checked); });
    });
    document.addEventListener('input', function(e) {
        if (e.target && e.target.classList.contains('input-phone')) {
            if (toggleInput) updateNoticeVisibility(toggleInput.checked);
        }
    });

    // Live Preview color sync
    var colorPicker    = document.getElementById('mb-color-picker');
    var colorHex       = document.getElementById('mb-color-hex');
    var colorIndicator = document.querySelector('.mb-color-indicator');
    var previewHeader  = document.getElementById('preview-header');
    var previewCallIcon   = document.getElementById('preview-call-icon');
    var previewHeaderIcon = document.querySelector('#whp-admin-panel .whp-v2-header-icon');

    function hexToRgb(hex) {
        var r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
        return r+','+g+','+b;
    }

    function updateColor(hexValue) {
        if (hexValue.indexOf('#') !== 0) hexValue = '#' + hexValue;
        if (/^#[0-9A-F]{6}$/i.test(hexValue)) {
            colorPicker.value = hexValue;
            colorHex.value = hexValue;
            if (colorIndicator) colorIndicator.style.backgroundColor = hexValue;
            if (previewHeader)     previewHeader.style.background = hexValue;

            const previewHotlineBtn = document.getElementById('preview-hotline-btn');
            if (previewHotlineBtn) previewHotlineBtn.style.background = hexValue;

            const callIcon = document.getElementById('preview-call-icon');
            if (callIcon) callIcon.style.color = hexValue;

            if (previewHeaderIcon) previewHeaderIcon.style.color = hexValue;

            // Footer note đổi màu theo
            var rgb = hexToRgb(hexValue);
            var footerNote = document.getElementById('preview-footer-note');
            var footerIcon = document.getElementById('preview-footer-icon');
            if (footerNote) {
                footerNote.style.background   = 'rgba('+rgb+',0.08)';
                footerNote.style.borderColor  = 'rgba('+rgb+',0.3)';
                footerNote.style.color        = hexValue;
            }
            if (footerIcon) footerIcon.style.color = hexValue;
        }
    }

    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() { updateColor(this.value); });
        colorHex.addEventListener('input', function() { updateColor(this.value); });
    }

    // Lời chào -> cập nhật preview header greeting
    var greetingInput   = document.getElementById('input_design_greeting');
    var previewGreeting = document.getElementById('preview-greeting');
    if (greetingInput && previewGreeting) {
        greetingInput.addEventListener('input', function() {
            previewGreeting.textContent = this.value || '<?php echo esc_js(__('Chúng tôi luôn sẵn sàng hỗ trợ bạn', 'whp')); ?>';
        });
    }

    // Tiêu đề chính -> cập nhật preview header title
    var phoneTitle       = document.getElementById('input_phone_title');
    var previewHdrTitle  = document.getElementById('preview-header-title');
    if (phoneTitle && previewHdrTitle) {
        phoneTitle.addEventListener('input', function() {
            previewHdrTitle.textContent = this.value || '<?php echo esc_js(__('Hỗ trợ trực tuyến', 'whp')); ?>';
        });
    }

    // Trạng thái online -> cập nhật preview online badge & status badge
    var onlineStatusInput = document.getElementById('input_online_status_text');
    var previewOnlineBadge = document.getElementById('preview-online-badge-text');
    var statusBadgeHeader = document.getElementById('preview-online-status-badge');

    function updateOnlineBadge(val) {
        var statusSelectDot = document.getElementById('status-select-dot');
        if (statusSelectDot) statusSelectDot.style.background = (val === 'Đang off') ? '#ef4444' : '#22c55e';
        var dot = document.querySelector('.whp-v2-online-badge-admin span');
        var badgeContainer = document.querySelector('.whp-v2-online-badge-admin');
        
        var previewDot = document.querySelector('.whp-v2-online-badge .whp-v2-dot');
        var previewBadgeContainer = document.querySelector('.whp-v2-online-badge');
        
        if (val === 'Đang off') {
            if (statusBadgeHeader) statusBadgeHeader.textContent = whpContactI18n.offline;
            if (previewOnlineBadge) previewOnlineBadge.textContent = whpContactI18n.offline;
            if (badgeContainer) {
                badgeContainer.style.background = '#fef2f2';
                badgeContainer.style.color = '#ef4444';
                badgeContainer.style.borderColor = '#fca5a5';
            }
            if (dot) dot.style.background = '#ef4444';
            if (previewDot) previewDot.style.background = '#ef4444';
            if (previewBadgeContainer) {
                previewBadgeContainer.style.background = 'rgba(239, 68, 68, 0.15)';
                previewBadgeContainer.style.color = '#ef4444';
            }
        } else {
            if (statusBadgeHeader) statusBadgeHeader.textContent = whpContactI18n.online;
            if (previewOnlineBadge) previewOnlineBadge.textContent = whpContactI18n.online;
            if (badgeContainer) {
                badgeContainer.style.background = '#eafaf1';
                badgeContainer.style.color = '#22c55e';
                badgeContainer.style.borderColor = '#c2f0d5';
            }
            if (dot) dot.style.background = '#22c55e';
            if (previewDot) previewDot.style.background = '#22c55e';
            if (previewBadgeContainer) {
                previewBadgeContainer.style.background = '';
                previewBadgeContainer.style.color = '';
            }
        }
        updateCtaNotePreview();
    }

    if (onlineStatusInput) {
        onlineStatusInput.addEventListener('change', function() {
            updateOnlineBadge(this.value);
        });
        updateOnlineBadge(onlineStatusInput.value);
    }

    // Helper: position a fixed dropdown menu under its trigger
    function positionDropdownMenu(trigger, menu) {
        var rect = trigger.getBoundingClientRect();
        menu.style.top   = (rect.bottom + 4) + 'px';
        menu.style.left  = rect.left + 'px';
        menu.style.width = rect.width + 'px';
    }

    // Custom dropdown: Trạng thái online
    (function() {
        var dropdown    = document.getElementById('status-dropdown');
        var trigger     = document.getElementById('status-trigger');
        var menu        = dropdown ? dropdown.querySelector('.mb-wph-custom-dropdown-menu') : null;
        var hiddenInput = document.getElementById('input_online_status_text');
        var displayLabel = document.getElementById('status-display-label');
        var dot         = document.getElementById('status-select-dot');
        if (!dropdown || !trigger || !menu) return;

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = dropdown.classList.contains('open');
            document.querySelectorAll('.mb-wph-custom-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
            if (!isOpen) { positionDropdownMenu(trigger, menu); dropdown.classList.add('open'); }
        });
        dropdown.querySelectorAll('.mb-wph-custom-dropdown-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                var val   = this.getAttribute('data-value');
                var color = this.getAttribute('data-dot');
                hiddenInput.value = val;
                displayLabel.textContent = val;
                if (dot) dot.style.background = color;
                dropdown.querySelectorAll('.mb-wph-custom-dropdown-option').forEach(function(o) { o.classList.remove('selected'); });
                this.classList.add('selected');
                dropdown.classList.remove('open');
                hiddenInput.dispatchEvent(new Event('change'));
            });
        });
        document.addEventListener('click', function() { dropdown.classList.remove('open'); });
        document.addEventListener('scroll', function() {
            if (dropdown.classList.contains('open')) positionDropdownMenu(trigger, menu);
        }, true);
    })();

    // Custom dropdown: Hiệu ứng mở popup
    (function() {
        var dropdown    = document.getElementById('effect-dropdown');
        var trigger     = document.getElementById('effect-trigger');
        var menu        = dropdown ? dropdown.querySelector('.mb-wph-custom-dropdown-menu') : null;
        var hiddenInput = document.getElementById('input_popup_effect');
        var displayLabel = document.getElementById('effect-display-label');
        if (!dropdown || !trigger || !menu) return;

        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            var isOpen = dropdown.classList.contains('open');
            document.querySelectorAll('.mb-wph-custom-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
            if (!isOpen) { positionDropdownMenu(trigger, menu); dropdown.classList.add('open'); }
        });
        dropdown.querySelectorAll('.mb-wph-custom-dropdown-option').forEach(function(opt) {
            opt.addEventListener('click', function(e) {
                e.stopPropagation();
                var val = this.getAttribute('data-value');
                hiddenInput.value = val;
                displayLabel.textContent = this.textContent.trim();
                dropdown.querySelectorAll('.mb-wph-custom-dropdown-option').forEach(function(o) { o.classList.remove('selected'); });
                this.classList.add('selected');
                dropdown.classList.remove('open');
            });
        });
        document.addEventListener('click', function() { dropdown.classList.remove('open'); });
        document.addEventListener('scroll', function() {
            if (dropdown.classList.contains('open')) positionDropdownMenu(trigger, menu);
        }, true);
    })();

    // Tiêu đề nút hotline -> cập nhật nút hotline
    var phoneBtnTextInput = document.getElementById('input_phone_btn_text');
    if (phoneBtnTextInput) {
        phoneBtnTextInput.addEventListener('input', function() {
            var val = this.value || whpContactI18n.hotlineBtnDefault;
            $('.whp-v2-call-label').text(val);
        });
    }

    // Ghi chú hotline -> cập nhật preview cta note
    var phoneCtaNoteInput = document.getElementById('input_phone_cta_note');
    var phoneCtaNoteOfflineInput = document.getElementById('input_phone_cta_note_offline');
    var previewCtaNote = document.getElementById('preview-cta-note');

    function updateCtaNotePreview() {
        if (!previewCtaNote) return;
        var status = onlineStatusInput ? onlineStatusInput.value : whpContactI18n.online;
        if (status === 'Đang off') {
            previewCtaNote.textContent = (phoneCtaNoteOfflineInput ? phoneCtaNoteOfflineInput.value : '') || '<?php echo esc_js(__('Hiện tại ngoại tuyến', 'whp')); ?>';
        } else {
            previewCtaNote.textContent = (phoneCtaNoteInput ? phoneCtaNoteInput.value : '') || '<?php echo esc_js(__('Hỗ trợ nhanh chóng 24/7', 'whp')); ?>';
        }
    }

    if (phoneCtaNoteInput) {
        phoneCtaNoteInput.addEventListener('input', updateCtaNotePreview);
    }
    if (phoneCtaNoteOfflineInput) {
        phoneCtaNoteOfflineInput.addEventListener('input', updateCtaNotePreview);
    }

    // Trình lắng nghe thay đổi các kênh liên hệ
    function updateSocialPreview() {
        const channels = [
            { id: 'input_other_zalo', activeName: 'whp_contact_other_zalo_active', selector: '.preview-zalo-card' },
            { id: 'input_other_facebook', activeName: 'whp_contact_other_facebook_active', selector: '.preview-facebook-card' },
            { id: 'input_other_messenger', activeName: 'whp_contact_other_messenger_active', selector: '.preview-messenger-card' },
            { id: 'input_other_email', activeName: 'whp_contact_other_email_active', selector: '.preview-email-card' }
        ];

        let anyVisible = false;
        channels.forEach(ch => {
            const input = document.getElementById(ch.id);
            const activeInput = document.querySelector(`input[name="${ch.activeName}"]`);
            const card = document.querySelector(ch.selector);

            if (input && activeInput && card) {
                const isShow = activeInput.checked && input.value.trim() !== '';
                card.style.display = isShow ? '' : 'none';
                if (isShow) anyVisible = true;
            }
        });
        const divider = document.getElementById('preview-social-divider');
        if (divider) divider.style.display = anyVisible ? '' : 'none';
    }

    $(document).on('input change', '#input_other_zalo, #input_other_facebook, #input_other_messenger, #input_other_email, .channel-toggle-input', function() {
        updateSocialPreview();
    });

    // Character counters helper
    function initCharCounters() {
        const items = [
            { inputId: 'input_phone_title', countId: 'len_phone_title' },
            { inputId: 'input_design_greeting', countId: 'len_design_greeting' },
            { inputId: 'input_phone_btn_text', countId: 'len_phone_btn_text' },
            { inputId: 'input_phone_cta_note', countId: 'len_phone_cta_note' },
            { inputId: 'input_phone_cta_note_offline', countId: 'len_phone_cta_note_offline' }
        ];
        items.forEach(item => {
            const input = document.getElementById(item.inputId);
            const counter = document.getElementById(item.countId);
            if (input && counter) {
                const update = () => {
                    counter.textContent = input.value.length;
                };
                input.addEventListener('input', update);
                update();
            }
        });
    }
    initCharCounters();

    // Vị trí hiển thị Segmented Control
    const segLeft = document.getElementById('seg-left');
    const segRight = document.getElementById('seg-right');
    const positionXInput = document.getElementById('mb-position-x');
    if (segLeft && segRight && positionXInput) {
        segLeft.addEventListener('click', function() {
            segLeft.classList.add('active');
            segRight.classList.remove('active');
            positionXInput.value = 'left';
        });
        segRight.addEventListener('click', function() {
            segRight.classList.add('active');
            segLeft.classList.remove('active');
            positionXInput.value = 'right';
        });
    }

    // Slider range độ cao
    var rangeSlider = document.getElementById('mb-position-y-slider');
    var rangeValText = document.getElementById('mb-position-y-val');
    var positionYInput = document.getElementById('mb-position-y');
    if (rangeSlider && rangeValText && positionYInput) {
        rangeSlider.addEventListener('input', function() {
            rangeValText.textContent = this.value;
            positionYInput.value = this.value;
        });
    }

    // Modal employee functions
    const modal = document.getElementById('wph-agent-modal');
    const modalTitle = document.getElementById('modal-agent-title');
    const modalRowId = document.getElementById('modal-agent-row-id');
    const modalName = document.getElementById('modal-agent-name');
    const modalPhone = document.getElementById('modal-agent-phone');

    function openModal(mode, rowId = '') {
        if (mode === 'add' && $('#agent-list-container .agent-list-row').length >= 3) {
            alert('<?php echo esc_js(__('Chỉ cho phép thêm tối đa 3 nhân viên!', 'whp')); ?>');
            return;
        }
        modalRowId.value = rowId;
        if (mode === 'edit') {
            modalTitle.textContent = '<?php echo esc_js(__('Chỉnh sửa nhân viên', 'whp')); ?>';
            const row = $(`.agent-list-row[data-id="${rowId}"]`);
            const avatar = row.find('.input-avatar').val();
            const name = row.find('.input-title').val();
            const phone = row.find('.input-phone').val();
            
            modalName.value = name;
            modalPhone.value = phone;
            $(`input[name="modal_avatar"][value="${avatar}"]`).prop('checked', true);
        } else {
            modalTitle.textContent = '<?php echo esc_js(__('Thêm nhân viên', 'whp')); ?>';
            modalName.value = '';
            modalPhone.value = '';
            $('input[name="modal_avatar"][value="contact-avata-women"]').prop('checked', true);
        }
        $(modal).fadeIn(200);
    }

    function closeModal() {
        $(modal).fadeOut(150);
    }

    $('#btnAddMorePhone').on('click', function() {
        openModal('add');
    });

    $(document).on('click', '.btn-edit-agent', function() {
        const rowId = $(this).closest('.agent-list-row').data('id');
        openModal('edit', rowId);
    });

    $('#modal-agent-cancel, #modal-agent-close-btn, .wph-modal-overlay').on('click', closeModal);

    $('#modal-agent-save').on('click', function() {
        const nameVal = modalName.value.trim();
        const phoneVal = modalPhone.value.trim();
        const avatarVal = $('input[name="modal_avatar"]:checked').val();
        
        if (!nameVal || !phoneVal) {
            alert('<?php echo esc_js(__('Vui lòng nhập đầy đủ thông tin tên và số điện thoại!', 'whp')); ?>');
            return;
        }

        let avatar_img = MB_WHP_URL_ASSETS + 'assets/admin/images/nu.svg';
        if (avatarVal === 'contact-avata-men') {
            avatar_img = MB_WHP_URL_ASSETS + 'assets/admin/images/nam.svg';
        } else if (avatarVal === 'contact-avata-support') {
            avatar_img = MB_WHP_URL_ASSETS + 'assets/admin/images/24.svg';
        }

        const rowId = modalRowId.value;
        if (rowId) {
            // Edit mode
            const row = $(`.agent-list-row[data-id="${rowId}"]`);
            row.find('.display-avatar-img').attr('src', avatar_img);
            row.find('.display-name').text(nameVal);
            row.find('.display-phone').text(phoneVal);
            
            row.find('.input-avatar').val(avatarVal);
            row.find('.input-title').val(nameVal);
            row.find('.input-phone').val(phoneVal);
        } else {
            // Add mode
            const nextId = parseInt($('#btnAddMorePhone').attr('data-number')) + 1;
            $('#btnAddMorePhone').attr('data-number', nextId);
            
            const newRowHtml = `
                <div class="agent-list-row" data-id="${nextId}">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center;">
                            <img src="${avatar_img}" style="width: 28px; height: 28px; object-fit: contain;" class="display-avatar-img">
                        </div>
                        <div>
                            <strong class="display-name" style="display: block; font-size: 13.5px; font-weight: 700; color: #0f172a;">${nameVal}</strong>
                            <span class="display-phone" style="display: block; font-size: 12.5px; color: #64748b; margin-top: 2px;">${phoneVal}</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="color: #22c55e; font-size: 12px; font-weight: 600; display: flex; align-items: center; gap: 4px; user-select: none;">
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: #22c55e;"></span>
                            Online
                        </span>
                        <button type="button" class="agent-btn-action btn-edit-agent"><i class="fas fa-pencil-alt" style="font-size: 12px;"></i></button>
                        <button type="button" class="agent-btn-action btn-delete-agent"><i class="fas fa-trash-alt" style="font-size: 12px;"></i></button>
                    </div>
                    
                    <!-- Hidden fields -->
                    <input type="hidden" name="whp_contact_phone_data[${nextId}][avatar]" class="input-avatar" value="${avatarVal}">
                    <input type="hidden" name="whp_contact_phone_data[${nextId}][title]" class="input-title" value="${nameVal}">
                    <input type="hidden" name="whp_contact_phone_data[${nextId}][phone]" class="input-phone" value="${phoneVal}">
                </div>
            `;
            $('#agent-list-container').append(newRowHtml);
        }

        closeModal();
        updatePreviewAgents();
    });

    $(document).on('click', '.btn-delete-agent', function() {
        if (confirm('<?php echo esc_js(__('Bạn có chắc chắn muốn xóa tư vấn viên này không?', 'whp')); ?>')) {
            $(this).closest('.agent-list-row').remove();
            updatePreviewAgents();
        }
    });

    // Update real-time preview agents list
    function updatePreviewAgents() {
        const agents = [];
        $('#agent-list-container .agent-list-row').each(function() {
            const avatar = $(this).find('.input-avatar').val();
            const title = $(this).find('.input-title').val();
            const phone = $(this).find('.input-phone').val();
            agents.push({ avatar, title, phone });
        });
        
        const previewBtnContainer = $('#preview-hotline-btn-container');
        const colorVal = colorHex.value || '#00c217';
        const labelVal = phoneBtnTextInput.value || whpContactI18n.hotlineBtnDefault;

        if (agents.length > 1) {
            let collapseRows = '';
            agents.forEach(function(agent) {
                let avSvg = 'nu.svg';
                if (agent.avatar === 'contact-avata-men') avSvg = 'nam.svg';
                else if (agent.avatar === 'contact-avata-support') avSvg = '24.svg';
                
                collapseRows += `
                    <a href="tel:${agent.phone}" class="whp-agent-row">
                        <img src="${MB_WHP_URL_ASSETS}assets/admin/images/${avSvg}" alt="Avatar">
                        <div class="whp-agent-info">
                            <strong>${agent.title}</strong>
                            <span>${agent.phone}</span>
                        </div>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                `;
            });

            previewBtnContainer.html(`
                <button type="button" class="whp-v2-call-btn whp-toggle-phones-btn" id="preview-hotline-btn" style="background:${colorVal};">
                    <span class="whp-v2-call-icon" id="preview-call-icon" style="color:${colorVal};"><i class="fas fa-phone-alt"></i></span>
                    <span class="whp-v2-call-label">${labelVal}</span>
                    <i class="fas fa-chevron-right whp-v2-chevron"></i>
                </button>
                <div class="whp-agents-collapse" id="preview-agents-collapse" style="display:none;">
                    <div class="whp-agents-collapse-inner">
                        ${collapseRows}
                    </div>
                </div>
            `);
        } else if (agents.length === 1) {
            previewBtnContainer.html(`
                <a href="tel:${agents[0].phone}" class="whp-v2-call-btn" id="preview-hotline-btn" style="background:${colorVal};">
                    <span class="whp-v2-call-icon" id="preview-call-icon" style="color:${colorVal};"><i class="fas fa-phone-alt"></i></span>
                    <span class="whp-v2-call-label">${labelVal}</span>
                    <i class="fas fa-chevron-right whp-v2-chevron"></i>
                </a>
            `);
        } else {
            previewBtnContainer.html(`
                <div class="whp-v2-call-btn" id="preview-hotline-btn" style="background:${colorVal}; opacity:.6; cursor:default;">
                    <span class="whp-v2-call-icon" id="preview-call-icon" style="color:${colorVal};"><i class="fas fa-phone-alt"></i></span>
                    <span class="whp-v2-call-label">${labelVal}</span>
                    <i class="fas fa-chevron-right whp-v2-chevron"></i>
                </div>
            `);
        }
        checkAgentCount();
    }

    // Toggle collapse agents list in preview
    $(document).on('click', '.whp-toggle-phones-btn', function() {
        var collapse = $('#preview-agents-collapse');
        if (collapse.length) {
            var isShown = collapse.css('display') !== 'none';
            collapse.slideToggle(180);
            var chevron = $(this).find('.whp-v2-chevron');
            if (chevron.length) {
                chevron.css('transform', isShown ? '' : 'rotate(90deg)');
            }
        }
    });

    function checkAgentCount() {
        const count = $('#agent-list-container .agent-list-row').length;
        if (count >= 3) {
            $('#btnAddMorePhone').hide();
        } else {
            $('#btnAddMorePhone').show();
        }
    }
    checkAgentCount();

    // Channel field format validation on form submit
    var mbForm = document.getElementById('mb-form');
    if (mbForm) {
        mbForm.addEventListener('submit', function(e) {
            var phoneRe = /^(\+84|0)\d{8,10}$/;
            var urlRe   = /^https?:\/\/.{3,}/;
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
            var chFields = [
                {id:'input_other_zalo',      re:phoneRe, msg:'<?php echo esc_js(__('Số Zalo không hợp lệ. Ví dụ: 0901234567', 'whp')); ?>'},
                {id:'input_other_facebook',  re:urlRe,   msg:'<?php echo esc_js(__('Link Facebook phải bắt đầu bằng https://', 'whp')); ?>'},
                {id:'input_other_messenger', re:urlRe,   msg:'<?php echo esc_js(__('Link Messenger phải bắt đầu bằng https://', 'whp')); ?>'},
                {id:'input_other_email',     re:emailRe, msg:'<?php echo esc_js(__('Email không hợp lệ. Ví dụ: support@example.com', 'whp')); ?>'}
            ];
            var firstErr = null;
            chFields.forEach(function(f) {
                var inp = document.getElementById(f.id);
                if (!inp) return;
                var val = inp.value.trim();
                if (val && !f.re.test(val)) {
                    inp.style.borderColor = '#ef4444';
                    inp.style.boxShadow   = '0 0 0 2px rgba(239,68,68,0.2)';
                    if (!firstErr) firstErr = f.msg;
                } else {
                    inp.style.borderColor = '';
                    inp.style.boxShadow   = '';
                }
            });
            if (firstErr) {
                e.preventDefault();
                whpToast(firstErr, 'error');
            }
        });
        // Clear error styles on input
        ['input_other_zalo','input_other_facebook','input_other_messenger','input_other_email'].forEach(function(id) {
            var inp = document.getElementById(id);
            if (inp) inp.addEventListener('input', function() {
                inp.style.borderColor = '';
                inp.style.boxShadow   = '';
            });
        });
    }

})(jQuery);
</script>

<?php whp_get_shared('footer'); ?>
