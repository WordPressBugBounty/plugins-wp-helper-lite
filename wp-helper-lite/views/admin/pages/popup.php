<?php if (!defined('ABSPATH')) exit; ?>
<?php whp_get_shared('header'); ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
/* === Pop-up Admin - Modern mb- Layout (matches maintenance page) === */
.mb-wph-pp-wrap {
    font-family: inherit;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 0 40px;
    box-sizing: border-box;
}

/* Header card with toggle */
.mb-wph-pp-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px 24px;
    margin-bottom: 20px;
    box-shadow: 0 4px 20px -2px rgba(15,23,42,.03), 0 2px 6px -1px rgba(15,23,42,.01);
}
/* .mb-wph-pp-header-left — defined in header card section below */
.mb-wph-pp-toggle-wrap { display: flex; align-items: center; gap: 12px; }
.mb-wph-pp-toggle-label { font-size: 13px; color: #64748b; font-weight: 700; transition: color 0.2s; }
.mb-wph-pp-toggle-label.active { color: #22c55e; }
.mb-wph-pp-toggle-switch { position: relative; display: inline-block; width: 52px; height: 28px; }
.mb-wph-pp-toggle-switch input { opacity: 0; width: 0; height: 0; }
.mb-wph-pp-toggle-slider {
    position: absolute; cursor: pointer;
    inset: 0; background: #cbd5e1; border-radius: 28px; transition: .3s ease;
}
.mb-wph-pp-toggle-slider:before {
    position: absolute; content: "";
    height: 20px; width: 20px; left: 4px; bottom: 4px;
    background: #fff; border-radius: 50%;
    transition: .3s ease; box-shadow: 0 1px 4px rgba(15,23,42,.15);
}
.mb-wph-pp-toggle-switch input:checked + .mb-wph-pp-toggle-slider { background: #22c55e; }
.mb-wph-pp-toggle-switch input:checked + .mb-wph-pp-toggle-slider:before { transform: translateX(24px); }

/* 2-col layout */
.mb-wph-pp-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 22px;
    align-items: start;
    margin-bottom: 0;
}
@media (max-width: 1100px) { .mb-wph-pp-layout { grid-template-columns: 1fr; } }
.mb-wph-pp-layout.mb-disabled { opacity:0.4; pointer-events:none; user-select:none; transition:opacity 0.3s; }

/* === Header card (giống Kênh liên hệ) === */
.mb-wph-pp-header-card {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #fff7ed 45%, #ffedd5 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(249,115,22,0.1), 0 0 0 1px #fed7aa;
    margin-bottom: 20px;
    overflow: hidden;
    min-height: 168px;
    display: flex;
    align-items: stretch;
}
.mb-wph-pp-header-left {
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
.mb-wph-pp-header-right {
    position: absolute;
    inset: 0 0 0 38%;
    overflow: hidden;
    pointer-events: none;
}
.mb-wph-pp-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mb-wph-pp-icon-box {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f97316, #ea580c);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(249,115,22,0.35);
}
.mb-wph-pp-title { font-size: 24px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.4px; }
.mb-wph-pp-subtitle { color: #64748b; font-size: 13.5px; line-height: 1.6; margin: 0; padding-left: 58px; max-width: 400px; }
.mb-wph-pp-toggle-inline { display: inline-flex; align-items: center; gap: 10px; padding-left: 58px; }

/* Card */
.mb-wph-pp-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 16px;
    box-shadow: 0 4px 20px -2px rgba(15,23,42,.03), 0 2px 6px -1px rgba(15,23,42,.01);
}
.mb-wph-pp-card:last-child { margin-bottom: 0; }
.mb-wph-pp-card h3 {
    font-size: 14.5px; font-weight: 700;
    color: #0f172a; margin: 0 0 20px;
    display: flex; align-items: center; gap: 10px;
    border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;
}

/* Section numbered icon — same pattern as Kênh liên hệ */
.mb-wph-pp-card.mb-wph-section-card { border-left: 4px solid transparent; transition: box-shadow 0.2s; }
.mb-wph-pp-card.accent-blue     { border-left-color: #3858e9; }
.mb-wph-pp-card.accent-orange   { border-left-color: #f97316; }
.mb-wph-pp-card.accent-green    { border-left-color: #22c55e; }
.mb-wph-pp-card.accent-purple   { border-left-color: #6366f1; }
.mb-wph-pp-card.accent-darkblue { border-left-color: #1d4ed8; }
.mb-wph-pp-card .mb-wph-section-header {
    display: flex; justify-content: space-between; align-items: center;
    border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 20px;
}
.mb-wph-pp-card .mb-wph-section-header-left { display: flex; gap: 12px; align-items: flex-start; }
.mb-wph-pp-card .mb-wph-section-icon {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 15px; font-weight: 700; color: #fff; line-height: 1;
}
.mb-wph-pp-card.accent-blue     .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(56,88,233,0.18),  0 0 0 10px rgba(56,88,233,0.08); }
.mb-wph-pp-card.accent-orange   .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(249,115,22,0.18), 0 0 0 10px rgba(249,115,22,0.08); }
.mb-wph-pp-card.accent-green    .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(34,197,94,0.18),  0 0 0 10px rgba(34,197,94,0.08); }
.mb-wph-pp-card.accent-purple   .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(99,102,241,0.18), 0 0 0 10px rgba(99,102,241,0.08); }
.mb-wph-pp-card.accent-darkblue .mb-wph-section-icon { box-shadow: 0 0 0 5px rgba(29,78,216,0.18),  0 0 0 10px rgba(29,78,216,0.08); }
.mb-wph-pp-card .mb-wph-section-header-text h3 {
    margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;
    border-bottom: none; padding-bottom: 0;
}
.mb-wph-pp-card .mb-wph-section-header-text p {
    margin: 3px 0 0; font-size: 12.5px; color: #64748b; line-height: 1.5;
}

/* Type selector: 2 cards */
.mb-wph-pp-type-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.mb-wph-pp-type-card {
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    cursor: pointer;
    transition: all .22s ease;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
}
.mb-wph-pp-type-card:hover {
    border-color: #93c5fd;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,.09);
}
.mb-wph-pp-type-card.selected {
    border-color: #3858e9;
    box-shadow: 0 6px 22px rgba(56,88,233,.2);
    transform: translateY(-1px);
}

/* ── Type card thumbnail ── */
.mb-wph-pp-thumb {
    padding-top: 72%;
    position: relative;
    overflow: hidden;
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
}
.mb-wph-pp-thumb-inner {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center;
}
/* Colored top accent strip per type */
.mb-wph-pp-type-accent {
    position: absolute; top: 0; left: 0; right: 0;
    height: 3px; z-index: 5;
}
/* Selected checkmark badge */
.mb-wph-pp-selected-badge {
    position: absolute; top: 10px; right: 10px; z-index: 10;
    width: 22px; height: 22px; border-radius: 50%;
    background: #22c55e;
    display: none; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(34,197,94,.5);
    animation: ppmBadgePop .2s cubic-bezier(.34,1.56,.64,1);
}
.mb-wph-pp-type-card.selected .mb-wph-pp-selected-badge { display: flex; }
@keyframes ppmBadgePop {
    from { transform: scale(.4); opacity: 0; }
    to   { transform: scale(1);  opacity: 1; }
}

/* Simulated page background */
.ppm-page-bg {
    position: absolute; inset: 0;
    background: #f1f5f9;
    display: flex; flex-direction: column;
}
.ppm-page-nav {
    height: 14px; background: #fff;
    border-bottom: 1px solid #e2e8f0;
    display: flex; align-items: center; gap: 4px; padding: 0 8px;
    flex-shrink: 0;
}
.ppm-page-nav-dot { width: 4px; height: 4px; border-radius: 50%; }
.ppm-page-body {
    flex: 1; padding: 6px 8px; display: flex; flex-direction: column; gap: 3px;
}
.ppm-page-line {
    border-radius: 2px; background: #e2e8f0;
}
.ppm-page-overlay {
    position: absolute; inset: 0;
    background: rgba(15,23,42,.5);
    backdrop-filter: blur(1px);
}

/* ── Form modal mockup ── */
.mb-wph-ppm-form-modal {
    position: relative; z-index: 2;
    background: #fff; border-radius: 10px;
    padding: 12px 14px 10px;
    width: 76%; text-align: center;
    box-shadow: 0 12px 32px rgba(0,0,0,.28);
}
.mb-wph-ppm-form-modal .ppm-close {
    position: absolute; top: 5px; right: 8px;
    font-size: 12px; color: #94a3b8; line-height: 1;
    width: 14px; height: 14px; border-radius: 50%;
    background: #f1f5f9; display: flex; align-items: center; justify-content: center;
}
.mb-wph-ppm-form-modal .ppm-avatar {
    width: 24px; height: 24px; border-radius: 50%;
    background: linear-gradient(135deg,#3858e9,#818cf8);
    margin: 0 auto 5px; display: flex; align-items: center; justify-content: center;
}
.mb-wph-ppm-form-modal .ppm-title {
    font-size: 8px; font-weight: 800; color: #0f172a; margin-bottom: 2px; line-height: 1.3;
}
.mb-wph-ppm-form-modal .ppm-sub {
    font-size: 6px; color: #64748b; margin-bottom: 7px; line-height: 1.4;
}
.mb-wph-ppm-form-modal .ppm-field {
    background: #f8fafc; border: 1px solid #e2e8f0;
    border-radius: 4px; height: 11px; margin-bottom: 4px;
    width: 100%; box-sizing: border-box;
}
.mb-wph-ppm-form-modal .ppm-btn {
    background: linear-gradient(135deg,#3858e9,#6366f1);
    border-radius: 5px; height: 14px; width: 100%; margin-top: 2px;
    display: flex; align-items: center; justify-content: center;
}
.mb-wph-ppm-form-modal .ppm-btn-text {
    height: 4px; width: 36px; background: rgba(255,255,255,.7); border-radius: 2px;
}
.mb-wph-ppm-form-modal .ppm-divider {
    display: flex; align-items: center; gap: 4px;
    margin: 6px 0 4px; font-size: 6px; color: #94a3b8;
}
.mb-wph-ppm-form-modal .ppm-divider::before,
.mb-wph-ppm-form-modal .ppm-divider::after {
    content: ''; flex: 1; height: 1px; background: #e2e8f0;
}
.mb-wph-ppm-form-modal .ppm-socials {
    display: flex; gap: 5px; justify-content: center;
}
.mb-wph-ppm-form-modal .ppm-socials span {
    width: 18px; height: 18px; border-radius: 5px;
    border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center;
}

/* ── Banner modal mockup ── */
.mb-wph-ppm-banner-modal {
    position: relative; z-index: 2;
    width: 78%; border-radius: 10px; overflow: hidden;
    box-shadow: 0 12px 32px rgba(0,0,0,.3);
}
.mb-wph-ppm-banner-modal .ppm-close {
    position: absolute; top: 6px; right: 7px;
    font-size: 11px; color: #fff; line-height: 1; z-index: 3;
    width: 14px; height: 14px; border-radius: 50%;
    background: rgba(0,0,0,.35); display: flex; align-items: center; justify-content: center;
}
.mb-wph-ppm-banner-img {
    width: 100%; padding-top: 60%; position: relative;
    background: linear-gradient(135deg,#667eea 0%,#764ba2 50%,#f093fb 100%);
}
.mb-wph-ppm-banner-img::before {
    content: ''; position: absolute; inset: 0;
    background: radial-gradient(circle at 30% 40%, rgba(255,255,255,.15) 0%, transparent 60%);
}
.ppm-banner-badge {
    position: absolute; top: 7px; left: 8px;
    background: #f59e0b; color: #fff; font-size: 5.5px; font-weight: 800;
    padding: 2px 5px; border-radius: 3px; letter-spacing: .3px;
    text-transform: uppercase; z-index: 2;
}
.ppm-banner-content {
    position: absolute; bottom: 8px; left: 0; right: 0;
    text-align: center;
}
.ppm-banner-title-line {
    height: 6px; background: rgba(255,255,255,.9); border-radius: 3px;
    width: 55%; margin: 0 auto 3px;
}
.ppm-banner-sub-line {
    height: 4px; background: rgba(255,255,255,.55); border-radius: 2px;
    width: 38%; margin: 0 auto 6px;
}
.ppm-banner-cta {
    display: inline-block; background: #fff; border-radius: 4px;
    padding: 3px 10px;
}
.ppm-banner-cta-text {
    height: 4px; width: 28px; background: #764ba2; border-radius: 2px;
}

/* Type card info row */
.mb-wph-pp-type-info {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px solid #f1f5f9;
    transition: background .2s;
}
.mb-wph-pp-type-card.selected .mb-wph-pp-type-info { background: #f5f7ff; }
.mb-wph-pp-type-info-left { display: flex; align-items: center; gap: 10px; }
.mb-wph-pp-type-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: transform .2s;
}
.mb-wph-pp-type-card:hover .mb-wph-pp-type-icon { transform: scale(1.08); }
.mb-wph-pp-type-info-text { display: flex; flex-direction: column; gap: 1px; }
.mb-wph-pp-type-info-text strong { font-size: 13px; font-weight: 700; color: #0f172a; }
.mb-wph-pp-type-info-text span { font-size: 11px; color: #94a3b8; }
.mb-wph-pp-type-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 20px;
    padding: 4px 12px; font-size: 11.5px; font-weight: 600; color: #94a3b8;
    transition: all .2s; white-space: nowrap; flex-shrink: 0;
}
.mb-wph-pp-type-card.selected .mb-wph-pp-type-badge {
    background: #eff2fe; color: #3858e9; border-color: #a5b4fc;
}
.mb-wph-pp-type-card.selected .mb-wph-pp-type-badge::before {
    content: "✓"; font-weight: 800; margin-right: 3px;
}

/* mb-wph-field, mb-wph-input, mb-wph-hint, mb-wph-grid-2 — từ global app.css */

/* Form source selector */
.mb-wph-pp-source-grid {
    display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 4px;
}
.mb-wph-pp-source-card {
    border: 2px solid #e2e8f0; border-radius: 10px;
    padding: 14px 14px 12px; cursor: pointer;
    transition: all .2s; background: #fff; text-align: center;
}
.mb-wph-pp-source-card:hover { border-color: #94a3b8; background: #f8fafc; }
.mb-wph-pp-source-card.selected { border-color: #3858e9; background: #eff2fe; }
.mb-wph-pp-source-card .src-icon {
    font-size: 22px; margin-bottom: 6px; display: block;
}
.mb-wph-pp-source-card strong {
    display: block; font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 3px;
}
.mb-wph-pp-source-card span {
    font-size: 11.5px; color: #64748b; line-height: 1.4; display: block;
}
.mb-wph-pp-source-card.selected strong { color: #3858e9; }

/* ── Form selector redesign ── */
.mb-pp-form-field {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 16px 10px !important;
    margin-top: 4px !important;
}
.mb-pp-form-field > label.mb-wph-label-icon {
    margin-bottom: 10px !important;
    color: #374151 !important;
    font-size: 13px !important;
}
.mb-pp-form-select-wrap {
    position: relative;
    display: block;
    width: 100%;
}
.mb-pp-form-select-wrap select {
    width: 100% !important;
    padding: 11px 40px 11px 14px !important;
    border: 1.5px solid #d1d5db !important;
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
    line-height: 1.4 !important;
}
.mb-pp-form-select-wrap select:focus {
    border-color: #3858e9 !important;
    box-shadow: 0 0 0 3px rgba(56,88,233,.1) !important;
}
.mb-pp-form-select-chevron {
    position: absolute;
    right: 13px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6b7280;
    display: flex;
    align-items: center;
    background: #fff;
    padding-left: 4px;
}

/* ── Delay / Flow / Cookie cards ── */
.mb-pp-info-card {
    background: #fff;
    border: 1px solid #e0e7ff;
    border-radius: 14px;
    padding: 20px 22px;
    margin-top: 16px;
    position: relative;
    overflow: hidden;
}
.mb-pp-info-card + .mb-pp-info-card { margin-top: 12px; }

/* Card header — nằm ngoài info-card */
.mb-pp-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 28px;
    margin-bottom: 10px;
}
#mb-pp-info-section > .mb-pp-card-header:first-child {
    margin-top: 20px;
}
.mb-pp-card-icon {
    width: 34px; height: 34px;
    background: #ede9fe;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-pp-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e1b4b;
    margin: 0;
}

/* Delay card body */
.mb-pp-delay-body-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}
.mb-pp-delay-left { display: flex; align-items: center; gap: 12px; }
.mb-pp-delay-input-wrap {
    display: flex;
    align-items: center;
    border: 1.5px solid #c7d2fe;
    border-radius: 10px;
    background: #fff;
    overflow: hidden;
    width: 110px;
}
.mb-pp-delay-num {
    font-size: 28px !important;
    font-weight: 800 !important;
    color: #4338ca !important;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
    width: 64px !important;
    padding: 10px 0 10px 14px !important;
    text-align: left !important;
    -moz-appearance: textfield !important;
}
.mb-pp-delay-num::-webkit-inner-spin-button,
.mb-pp-delay-num::-webkit-outer-spin-button { -webkit-appearance: none; }
.mb-pp-delay-spinners {
    display: flex;
    flex-direction: column;
    padding: 4px 6px;
    gap: 2px;
}
.mb-pp-spin-btn {
    width: 20px; height: 20px;
    background: #ede9fe;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #6366f1;
    font-size: 10px;
    line-height: 1;
    transition: background .15s;
    padding: 0;
}
.mb-pp-spin-btn:hover { background: #c7d2fe; }
.mb-pp-delay-unit {
    font-size: 14px;
    font-weight: 600;
    color: #6366f1;
}
.mb-pp-delay-hint {
    font-size: 12px;
    color: #818cf8;
    margin-top: 10px;
    display: block;
}

/* Decorative illustration (right side of delay card) */
.mb-pp-deco {
    flex-shrink: 0;
    opacity: .85;
}

/* Flow steps — horizontal */
.mb-pp-flow-steps-h {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 4px;
}
.mb-pp-flow-step-h {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    flex: 1;
    min-width: 0;
}
.mb-pp-flow-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: #e0e7ff;
    color: #4f46e5;
    font-size: 12px;
    font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-pp-flow-num.active {
    background: #4f46e5;
    color: #fff;
    box-shadow: 0 4px 10px rgba(79,70,229,.35);
}
.mb-pp-flow-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.mb-pp-flow-icon.indigo { background: #ede9fe; }
.mb-pp-flow-icon.green  { background: #dcfce7; }
.mb-pp-flow-icon.red    { background: #fee2e2; }
.mb-pp-flow-icon.teal   { background: #ccfbf1; }
.mb-pp-flow-label {
    font-size: 11.5px;
    color: #374151;
    text-align: center;
    line-height: 1.4;
}
.mb-pp-flow-label.active { color: #4f46e5; font-weight: 700; }
.mb-pp-flow-arrow-h {
    padding-top: 26px;
    color: #a5b4fc;
    font-size: 16px;
    flex-shrink: 0;
    font-weight: 700;
}

/* Cookie card */
.mb-pp-cookie-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}
.mb-pp-cookie-label {
    font-size: 13px;
    color: #475569;
    font-weight: 500;
}
.mb-pp-cookie-badge {
    background: #ede9fe;
    color: #4338ca;
    border-radius: 6px;
    padding: 3px 10px;
    font-size: 13px;
    font-weight: 700;
    font-family: monospace;
}
.mb-pp-cookie-desc {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.55;
    margin: 0;
}
.mb-pp-cookie-body {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}
.mb-pp-hint-bar {
    margin-top: 14px;
    background: #eef2ff;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    color: #4338ca;
    display: flex;
    align-items: center;
    gap: 6px;
}
/* Info card shown after selection */
.mb-pp-form-info {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    min-height: 42px;
}
.mb-pp-form-info.hidden { display: none; }
.mb-pp-form-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .3px;
    white-space: nowrap;
    flex-shrink: 0;
}
.mb-pp-form-badge.cf7   { background: #fff3e0; color: #e65100; }
.mb-pp-form-badge.wpforms    { background: #e3f2fd; color: #1565c0; }
.mb-pp-form-badge.formidable { background: #f3e5f5; color: #7b1fa2; }
.mb-pp-form-badge.fluent     { background: #e8f5e9; color: #2e7d32; }
.mb-pp-form-badge.default    { background: #f1f5f9; color: #475569; }
.mb-pp-form-info-name {
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mb-pp-form-info-id {
    font-size: 11px;
    color: #94a3b8;
    white-space: nowrap;
    flex-shrink: 0;
}
/* Empty state */
.mb-pp-form-empty {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #fffbeb;
    border: 1.5px dashed #fcd34d;
    border-radius: 10px;
}
.mb-pp-form-empty-icon {
    width: 36px;
    height: 36px;
    background: #fef3c7;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 18px;
}
.mb-pp-form-empty-text strong {
    display: block;
    font-size: 13px;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 2px;
}
.mb-pp-form-empty-text span {
    font-size: 12px;
    color: #b45309;
}

.mb-wph-label-icon {
    display: flex; align-items: center; gap: 5px;
    font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px;
}

/* Upload zone — modern drag-drop style */
.mb-wph-pp-upload-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: all .2s ease;
    position: relative;
}
.mb-wph-pp-upload-zone:hover {
    border-color: #3858e9;
    background: #f0f4ff;
}
.mb-wph-pp-upload-zone-icon {
    width: 48px; height: 48px;
    background: #eff2fe;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
}
.mb-wph-pp-upload-zone-title {
    font-size: 13.5px; font-weight: 600; color: #334155; margin-bottom: 4px;
}
.mb-wph-pp-upload-zone-sub {
    font-size: 12px; color: #94a3b8; margin-bottom: 14px;
}
.mb-wph-pp-upload-zone-btn {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; border: 1.5px solid #3858e9; color: #3858e9;
    border-radius: 8px; padding: 7px 18px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all .2s;
}
.mb-wph-pp-upload-zone-btn:hover {
    background: #3858e9; color: #fff;
}

/* Preview state */
.mb-wph-pp-img-preview-wrap {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    border: 1.5px solid #e2e8f0;
    background: #f1f5f9;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mb-wph-pp-img-preview {
    width: 100%;
    max-height: 220px;
    object-fit: cover;
    display: block;
    border-radius: 8px;
}
.mb-wph-pp-img-preview[src=""] {
    display: none;
}
.mb-wph-pp-img-overlay {
    position: absolute; inset: 0;
    background: rgba(15,23,42,.45);
    opacity: 0; transition: opacity .2s;
    display: flex; align-items: center; justify-content: center; gap: 10px;
    border-radius: 8px;
}
.mb-wph-pp-img-preview-wrap:hover .mb-wph-pp-img-overlay { opacity: 1; }
.mb-wph-pp-img-action {
    display: inline-flex; align-items: center; gap: 5px;
    background: #fff; border: none; border-radius: 7px;
    padding: 6px 12px; font-size: 12.5px; font-weight: 600;
    cursor: pointer; transition: all .2s;
}
.mb-wph-pp-img-action.danger { color: #ef4444; }
.mb-wph-pp-img-action.danger:hover { background: #fee2e2; }
.mb-wph-pp-img-action.change { color: #3858e9; }
.mb-wph-pp-img-action.change:hover { background: #eff2fe; }

/* Section divider */
.mb-wph-pp-section-title {
    font-size: 13px; font-weight: 700; color: #0f172a;
    display: flex; align-items: center; gap: 8px;
    margin: 20px 0 14px; padding-top: 18px;
    border-top: 1px solid #f1f5f9;
}

/* Preview panel (right col) */
.mb-wph-pp-preview-panel { position: sticky; top: 32px; }
.mb-wph-pp-preview-card {
    border: 1px solid #fdba74;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 12px 40px rgba(249,115,22,0.15);
}
.mb-wph-pp-preview-header {
    background: linear-gradient(100deg, #fff7ed 0%, #ffedd5 60%, #fed7aa 100%);
    padding: 16px 20px 14px;
}
.mb-wph-pp-preview-header h3 {
    margin: 0 0 3px; font-size: 15px; font-weight: 700; color: #0f172a;
}
.mb-wph-pp-preview-header p {
    margin: 0; font-size: 13px; color: #78350f;
}
.mb-wph-pp-preview-window {
    overflow: hidden;
}
.mb-wph-pp-test-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 4px;
    margin-top: 10px;
}
.mb-wph-pp-test-bar-left { display: flex; align-items: center; gap: 8px; }
.mb-wph-pp-test-label { font-size: 13px; font-weight: 600; color: #475569; }
.mb-wph-pp-device-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 36px; height: 36px; border-radius: 8px;
    border: 1.5px solid #e2e8f0; background: #fff;
    cursor: pointer; color: #64748b; transition: all 0.15s;
}
.mb-wph-pp-device-btn.active { border-color: #f97316; background: #fff7ed; color: #f97316; }
.mb-wph-pp-device-btn:hover:not(.active) { border-color: #cbd5e1; background: #f8fafc; }
#mb_pp_device_label { font-size: 12px; font-weight: 600; color: #f97316; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 6px; padding: 3px 8px; }
.mb-wph-pp-reload-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 8px;
    border: 1.5px solid #e2e8f0; background: #fff;
    font-size: 13px; font-weight: 600; color: #475569;
    cursor: pointer; transition: all 0.15s;
}
.mb-wph-pp-reload-btn:hover { border-color: #f97316; color: #f97316; background: #fff7ed; }
.mb-wph-pp-cookie-note {
    display: flex; align-items: flex-start; gap: 10px;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 10px; padding: 12px 16px; margin-top: 10px;
}
.mb-wph-pp-cookie-note p { margin: 0; font-size: 12.5px; color: #166534; line-height: 1.6; }
.mb-wph-pp-cookie-note code { background: #dcfce7; padding: 1px 5px; border-radius: 4px; font-size: 11.5px; }
/* ── Sidebar info cards ── */
.mb-pp-sidebar-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 18px; margin-top: 12px;
}
.mb-pp-sidebar-card h4 {
    display: flex; align-items: center; gap: 8px;
    font-size: 13.5px; font-weight: 700; color: #0f172a; margin: 0 0 10px;
}
.mb-pp-sidebar-icon {
    width: 26px; height: 26px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.mb-pp-sidebar-text { font-size: 12.5px; color: #64748b; line-height: 1.65; margin: 0; }
.mb-pp-tips-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 9px; }
.mb-pp-tips-list li { display: flex; align-items: flex-start; gap: 8px; font-size: 12.5px; color: #475569; line-height: 1.5; }
.mb-pp-tips-list li::before { content: '✓'; color: #f97316; font-weight: 700; flex-shrink: 0; margin-top: 1px; }
.mb-pp-stat-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
.mb-pp-stat-row:last-child { border-bottom: none; padding-bottom: 0; }
.mb-pp-stat-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
.mb-pp-stat-label { font-size: 12px; color: #64748b; flex: 1; }
.mb-pp-stat-val { font-size: 12.5px; font-weight: 700; color: #0f172a; }
.mb-wph-pp-preview-bar {
    display: flex; align-items: center; justify-content: space-between;
    background: #f1f5f9;
    padding: 10px 16px;
    border-bottom: 1px solid #e2e8f0;
}
.mb-wph-pp-preview-bar span.mb-wph-pp-preview-title { color: #94a3b8; font-size: 12px; font-weight: 600; letter-spacing: 0.2px; }
.mb-wph-pp-preview-frame-wrap {
    overflow: hidden;
}

/* CSS popup live preview canvas */
.mb-wph-pp-preview-canvas {
    background: #f0f4f8;
    min-height: 680px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    box-sizing: border-box;
}
/* Simulated website bg lines */
.mb-wph-pp-preview-canvas::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(#e2e8f0 1px, transparent 1px),
        linear-gradient(90deg, #e2e8f0 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: .5;
}
.mb-wph-pp-overlay-mask {
    position: absolute; inset: 0;
    background: rgba(15,23,42,.5);
    z-index: 1;
}

/* Form popup live preview */
.mb-wph-pp-live-form-modal {
    position: relative; z-index: 2;
    background: #fff;
    border-radius: 16px;
    padding: 32px 28px 24px;
    width: 100%; max-width: 340px;
    box-shadow: 0 20px 60px rgba(0,0,0,.3);
    text-align: center;
}
.mb-wph-pp-live-form-modal .live-close {
    position: absolute; top: 12px; right: 14px;
    font-size: 20px; color: #94a3b8; cursor: default; line-height: 1;
}
.mb-wph-pp-live-form-modal .live-icon {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 24px;
}
.mb-wph-pp-live-form-modal .live-title {
    font-size: 18px; font-weight: 800; color: #0f172a; margin-bottom: 6px;
    line-height: 1.3;
}
.mb-wph-pp-live-form-modal .live-sub {
    font-size: 13.5px; color: #64748b; margin-bottom: 20px; line-height: 1.5;
}
.mb-wph-pp-live-form-modal .live-input {
    width: 100%; padding: 11px 14px; border: 1.5px solid #e2e8f0;
    border-radius: 8px; font-size: 13.5px; color: #64748b;
    background: #f8fafc; box-sizing: border-box; margin-bottom: 10px;
    font-family: inherit;
}
.mb-wph-pp-live-form-modal .live-btn {
    width: 100%; padding: 12px;
    background: linear-gradient(135deg, #3858e9, #2563eb);
    border: none; border-radius: 8px;
    font-size: 14px; font-weight: 700; color: #fff;
    cursor: default;
}
.mb-wph-pp-live-form-modal .live-socials {
    display: flex; gap: 8px; justify-content: center; margin-top: 16px;
}
.mb-wph-pp-live-form-modal .live-socials a {
    width: 30px; height: 30px; border-radius: 8px;
    background: #f1f5f9; display: flex; align-items: center; justify-content: center;
    font-size: 13px; text-decoration: none; color: #475569;
}

/* Banner popup live preview */
.mb-wph-pp-live-banner-modal {
    position: relative; z-index: 2;
    border-radius: 12px; overflow: hidden;
    width: 100%; max-width: 360px;
    box-shadow: 0 20px 60px rgba(0,0,0,.35);
}
.mb-wph-pp-live-banner-modal .live-close {
    position: absolute; top: 10px; right: 12px;
    font-size: 24px; color: #fff; cursor: default; z-index: 3; line-height: 1;
    text-shadow: 0 1px 4px rgba(0,0,0,.5);
}
.mb-wph-pp-live-banner-img {
    width: 100%; min-height: 220px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex; align-items: center; justify-content: center;
    font-size: 48px;
}
.mb-wph-pp-live-banner-img img {
    width: 100%; max-height: 300px; object-fit: cover; display: block;
}

/* Save bar */
.mb-wph-pp-save-bar {
    background: #fff; border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 20px;
    box-shadow: 0 4px 20px -2px rgba(15,23,42,.03);
}
.mb-wph-pp-save-note { font-size: 12.5px; color: #64748b; }
.mb-wph-pp-save-btn {
    background: linear-gradient(135deg, #3858e9 0%, #2563eb 100%);
    color: #fff; border: none; border-radius: 9px;
    padding: 11px 32px; font-size: 14px; font-weight: 600;
    cursor: pointer; box-shadow: 0 4px 14px rgba(56,88,233,.35);
    transition: all .2s; letter-spacing: .2px;
    display: inline-flex; align-items: center; gap: 7px;
}
.mb-wph-pp-save-btn:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-1px); box-shadow: 0 6px 20px rgba(56,88,233,.4);
}

/* ---- Preview: skeleton shimmer ---- */
.mb-wph-pp-skeleton {
    position: absolute; inset: 0;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%; animation: mb-pp-shimmer 1.4s infinite;
    display: flex; align-items: center; justify-content: center;
    flex-direction: column; gap: 10px; transition: opacity 0.3s ease; z-index: 2;
}
.mb-wph-pp-skeleton.hidden { opacity: 0; pointer-events: none; }
.mb-wph-pp-skeleton svg { opacity: 0.35; }
.mb-wph-pp-skeleton span { font-size: 12px; color: #94a3b8; font-weight: 500; }
@keyframes mb-pp-shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }

/* ---- Preview: browser bar ---- */
.mb-wph-browser-bar { height: 38px; background: #f1f5f9; border-bottom: 1px solid #dde1e7; display: flex; align-items: center; gap: 10px; padding: 0 14px; flex-shrink: 0; }
.mb-wph-browser-dots { display: flex; gap: 5px; align-items: center; flex-shrink: 0; }
.mb-wph-browser-dots span { display: inline-block; width: 10px; height: 10px; border-radius: 50%; }
.mb-wph-browser-dots span:nth-child(1) { background: #ff5f57; }
.mb-wph-browser-dots span:nth-child(2) { background: #ffbd2e; }
.mb-wph-browser-dots span:nth-child(3) { background: #28c940; }
.mb-wph-browser-url { flex: 1; min-width: 0; display: flex; align-items: center; gap: 5px; background: #fff; border: 1px solid #dde1e7; border-radius: 6px; padding: 4px 9px; font-size: 11.5px; color: #64748b; }
.mb-wph-browser-url svg { flex-shrink: 0; }
.mb-wph-browser-url span { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }


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
#mb-pp-info-section.mb-disabled{opacity:0.4;pointer-events:none;user-select:none;transition:opacity 0.3s;}
</style>
<div id="whp-toast-wrap"></div>

<?php
$pp_ts   = time();
$pp_hmac = hash_hmac('sha256', 'wpaap_popup_preview:' . $pp_ts, wp_salt('auth'));
$popup_preview_url = home_url('/') . '?wpaap_popup_preview=1&pt_ts=' . $pp_ts . '&pt_h=' . $pp_hmac;
?>
<form method="post" id="mb-popup-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-pp-wrap">

    <!-- Lưu ý: chế độ bảo trì -->
    <?php $maintenance_on = whp_get_option('whp_maintenance_active'); ?>
    <?php if ($maintenance_on) : ?>
    <div style="display:flex;align-items:flex-start;gap:10px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:16px;">
        <span class="dashicons dashicons-warning" style="color:#d97706;font-size:18px;width:18px;height:18px;flex-shrink:0;margin-top:1px;"></span>
        <div>
            <strong style="font-size:13px;color:#92400e;"><?php esc_html_e('Chế độ bảo trì đang BẬT', 'whp'); ?></strong>
            <p style="font-size:12.5px;color:#78350f;margin:2px 0 0;line-height:1.5;"><?php esc_html_e('Pop-up sẽ không hiển thị khi website đang ở chế độ bảo trì.', 'whp'); ?>
                <a href="<?php echo admin_url('admin.php?page=mb-wphelper-ai&subtab=maintenance'); ?>" style="color:#d97706;font-weight:600;"><?php esc_html_e('Tắt bảo trì →', 'whp'); ?></a>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="mb-wph-pp-header-card">
        <div class="mb-wph-pp-header-left">
            <div class="mb-wph-pp-title-row">
                <div class="mb-wph-pp-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="14" rx="2" stroke="#fff" stroke-width="2"/><rect x="7" y="8" width="8" height="2" rx="1" fill="#fff"/><rect x="7" y="12" width="5" height="2" rx="1" fill="#fff"/><circle cx="19" cy="5" r="3.5" fill="#fbbf24"/></svg>
                </div>
                <h1 class="mb-wph-pp-title" style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;">Pop-up</h1>
            </div>
            <p class="mb-wph-pp-subtitle"><?php echo wp_kses_post(__($itemInfo['desc'] ?? 'Cài đặt pop-up hiển thị trên website của bạn.', 'whp')); ?></p>
            <div class="mb-wph-pp-toggle-inline">
                <label class="mb-wph-pp-toggle-switch">
                    <input type="checkbox" name="whp_popup_active" id="mb_pp_toggle" value="1" <?php echo $whp_popup_active_check; ?>>
                    <span class="mb-wph-pp-toggle-slider"></span>
                </label>
                <span class="mb-wph-pp-toggle-label <?php echo $whp_popup_active ? 'active' : ''; ?>" id="mb_pp_toggle_label" style="font-size:13px;font-weight:600;">
                    <?php echo $whp_popup_active ? esc_html__('Đang bật', 'whp') : esc_html__('Đang tắt', 'whp'); ?>
                </span>
            </div>
        </div>
        <div class="mb-wph-pp-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="pbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#fff7ed" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#ffedd5" stop-opacity="0.5"/>
                        <stop offset="100%" stop-color="#fed7aa" stop-opacity="1"/>
                    </linearGradient>
                    <linearGradient id="popupCard" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#ffffff"/>
                        <stop offset="100%" stop-color="#f8fafc"/>
                    </linearGradient>
                    <linearGradient id="popupBtn" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f97316"/>
                        <stop offset="100%" stop-color="#ea580c"/>
                    </linearGradient>
                    <linearGradient id="browserWin" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#e2e8f0"/>
                        <stop offset="100%" stop-color="#cbd5e1"/>
                    </linearGradient>
                    <filter id="ps" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="4" stdDeviation="8" flood-color="rgba(249,115,22,0.18)"/>
                    </filter>
                    <filter id="pssm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="5" flood-color="rgba(249,115,22,0.15)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#pbg)"/>

                <!-- Star/sparkle top-right -->
                <g transform="translate(625,14)">
                    <path d="M8 0 L10 6 L16 8 L10 10 L8 16 L6 10 L0 8 L6 6 Z" fill="#fb923c" opacity="0.7"/>
                </g>
                <g transform="translate(648,8) scale(0.6)">
                    <path d="M8 0 L10 6 L16 8 L10 10 L8 16 L6 10 L0 8 L6 6 Z" fill="#fdba74" opacity="0.6"/>
                </g>
                <circle cx="610" cy="22" r="4" fill="rgba(249,115,22,0.25)"/>
                <circle cx="598" cy="12" r="3" fill="rgba(251,146,60,0.2)"/>

                <!-- ── Browser window (background) ── -->
                <rect x="270" y="30" width="200" height="130" rx="10" fill="#f1f5f9" filter="url(#ps)"/>
                <rect x="270" y="30" width="200" height="24" rx="10" fill="url(#browserWin)"/>
                <rect x="270" y="42" width="200" height="12" fill="url(#browserWin)"/>
                <circle cx="283" cy="42" r="4" fill="#ef4444" opacity="0.7"/>
                <circle cx="294" cy="42" r="4" fill="#f59e0b" opacity="0.7"/>
                <circle cx="305" cy="42" r="4" fill="#22c55e" opacity="0.7"/>
                <rect x="315" y="37" width="80" height="9" rx="4.5" fill="#e2e8f0"/>
                <!-- Browser content (blurred/faded) -->
                <rect x="279" y="62" width="130" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="279" y="74" width="160" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="279" y="86" width="110" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="279" y="98" width="140" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="279" y="110" width="90" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="279" y="122" width="120" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="279" y="134" width="80" height="7" rx="3.5" fill="#e2e8f0"/>

                <!-- ── Popup modal overlay ── -->
                <rect x="270" y="30" width="200" height="130" rx="10" fill="rgba(15,23,42,0.18)"/>
                <!-- Popup card -->
                <rect x="302" y="52" width="136" height="94" rx="10" fill="url(#popupCard)" filter="url(#ps)"/>
                <!-- Popup header orange -->
                <rect x="302" y="52" width="136" height="30" rx="10" fill="url(#popupBtn)"/>
                <rect x="302" y="68" width="136" height="14" fill="url(#popupBtn)"/>
                <circle cx="315" cy="67" r="7" fill="rgba(255,255,255,0.25)"/>
                <rect x="328" y="63" width="55" height="5" rx="2.5" fill="rgba(255,255,255,0.7)"/>
                <rect x="328" y="71" width="40" height="4" rx="2" fill="rgba(255,255,255,0.4)"/>
                <!-- Close button -->
                <circle cx="428" cy="62" r="6" fill="rgba(255,255,255,0.3)"/>
                <line x1="425" y1="59" x2="431" y2="65" stroke="white" stroke-width="1.5"/>
                <line x1="431" y1="59" x2="425" y2="65" stroke="white" stroke-width="1.5"/>
                <!-- Popup content -->
                <rect x="312" y="90" width="116" height="7" rx="3.5" fill="#f1f5f9"/>
                <rect x="312" y="102" width="96" height="7" rx="3.5" fill="#f1f5f9"/>
                <!-- CTA button -->
                <rect x="322" y="116" width="96" height="22" rx="8" fill="url(#popupBtn)"/>
                <rect x="346" y="123" width="48" height="7" rx="3.5" fill="rgba(255,255,255,0.85)"/>

                <!-- ── Dotted connections ── -->
                <line x1="248" y1="84" x2="270" y2="84" stroke="#fdba74" stroke-width="2" stroke-dasharray="5,5" opacity="0.8"/>
                <line x1="248" y1="84" x2="212" y2="50" stroke="#fdba74" stroke-width="2" stroke-dasharray="5,5" opacity="0.7"/>
                <line x1="212" y1="50" x2="172" y2="34" stroke="#fdba74" stroke-width="2" stroke-dasharray="5,5" opacity="0.6"/>
                <line x1="248" y1="84" x2="204" y2="116" stroke="#fdba74" stroke-width="2" stroke-dasharray="5,5" opacity="0.6"/>
                <line x1="248" y1="84" x2="165" y2="84" stroke="#fdba74" stroke-width="2" stroke-dasharray="5,5" opacity="0.55"/>

                <!-- ── Center icon: notification bell ── -->
                <circle cx="248" cy="84" r="42" fill="white" filter="url(#ps)"/>
                <circle cx="248" cy="84" r="34" fill="#fff7ed"/>
                <!-- Bell shape -->
                <path d="M248 60 C238 60 231 67 231 77 L231 88 L226 93 L270 93 L265 88 L265 77 C265 67 258 60 248 60 Z" fill="#f97316"/>
                <rect x="244" y="93" width="8" height="5" rx="2.5" fill="#ea580c"/>
                <circle cx="248" cy="97" r="3" fill="#c2410c"/>
                <!-- Notification dot -->
                <circle cx="260" cy="62" r="7" fill="#ef4444"/>
                <text x="260" y="66" text-anchor="middle" fill="white" font-size="8" font-weight="bold" font-family="sans-serif">3</text>

                <!-- ── Floating icon 1: orange badge ── -->
                <rect x="144" y="16" width="52" height="36" rx="10" fill="url(#popupBtn)" filter="url(#pssm)"/>
                <text x="170" y="38" text-anchor="middle" fill="white" font-family="monospace" font-size="11" font-weight="bold">POP</text>

                <!-- ── Floating icon 2: yellow star (top-right area) ── -->
                <rect x="480" y="10" width="44" height="44" rx="12" fill="#fbbf24" filter="url(#pssm)"/>
                <path d="M502 20 L505 29 L514 29 L507 35 L510 44 L502 38 L494 44 L497 35 L490 29 L499 29 Z" fill="white" opacity="0.85"/>

                <!-- ── Floating icon 3: green checkmark ── -->
                <rect x="162" y="104" width="44" height="44" rx="12" fill="#22c55e" filter="url(#pssm)"/>
                <path d="M172 126 L179 133 L192 118" stroke="white" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>

                <!-- ── Small node ── -->
                <circle cx="154" cy="84" r="16" fill="white" filter="url(#pssm)"/>
                <circle cx="154" cy="84" r="11" fill="#fff7ed"/>
                <circle cx="154" cy="84" r="6"  fill="#fdba74"/>

                <!-- ── Plant ── -->
                <rect x="633" y="148" width="7" height="18" rx="3.5" fill="#4ade80" opacity="0.9"/>
                <ellipse cx="628" cy="144" rx="14" ry="8"  fill="#22c55e" opacity="0.95" transform="rotate(-30 628 144)"/>
                <ellipse cx="644" cy="141" rx="12" ry="7"  fill="#4ade80" opacity="0.85" transform="rotate(22 644 141)"/>
                <ellipse cx="636" cy="134" rx="9"  ry="5.5" fill="#86efac" opacity="0.9"  transform="rotate(-8 636 134)"/>
                <rect x="630" y="154" width="7" height="14" rx="3.5" fill="#15803d" opacity="0.55"/>
            </svg>
        </div>
    </div>

    <!-- 2-col layout -->
    <div id="mb-popup-layout" class="mb-wph-pp-layout<?php echo !$whp_popup_active ? ' mb-disabled' : ''; ?>" style="margin-bottom:22px;">

        <!-- Left: type selector + settings -->
        <div>

            <!-- Type selector -->
            <div class="mb-wph-pp-card mb-wph-section-card accent-blue">
                <div class="mb-wph-section-header">
                    <div class="mb-wph-section-header-left">
                        <div class="mb-wph-section-icon" style="background:#3858e9;">1</div>
                        <div class="mb-wph-section-header-text">
                            <h3><?php esc_html_e('Chọn kiểu pop-up', 'whp'); ?></h3>
                        </div>
                    </div>
                </div>

                <div class="mb-wph-pp-type-grid">

                    <!-- Type 0: Form / Newsletter -->
                    <div class="mb-wph-pp-type-card <?php echo ($whp_popup_type == '0' || $whp_popup_type == '') ? 'selected' : ''; ?>"
                         data-type="0" tabindex="0" role="button">
                        <div class="mb-wph-pp-thumb" style="background: linear-gradient(135deg, #eef2ff 0%, #dbeafe 100%);">
                            <div class="mb-wph-pp-type-accent" style="background: linear-gradient(90deg, #3858e9, #818cf8);"></div>
                            <div class="mb-wph-pp-selected-badge">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="mb-wph-pp-thumb-inner">
                                <!-- Simulated website bg -->
                                <div class="ppm-page-bg">
                                    <div class="ppm-page-nav">
                                        <div class="ppm-page-nav-dot" style="background:#ef4444;"></div>
                                        <div class="ppm-page-nav-dot" style="background:#f59e0b;"></div>
                                        <div class="ppm-page-nav-dot" style="background:#22c55e;"></div>
                                    </div>
                                    <div class="ppm-page-body">
                                        <div class="ppm-page-line" style="height:5px;width:60%;"></div>
                                        <div class="ppm-page-line" style="height:4px;width:80%;"></div>
                                        <div class="ppm-page-line" style="height:4px;width:70%;"></div>
                                        <div class="ppm-page-line" style="height:4px;width:50%;"></div>
                                    </div>
                                </div>
                                <div class="ppm-page-overlay"></div>
                                <!-- Form modal -->
                                <div class="mb-wph-ppm-form-modal">
                                    <span class="ppm-close">×</span>
                                    <div class="ppm-avatar">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" fill="rgba(255,255,255,.3)"/><polyline points="22,6 12,13 2,6" stroke="#fff" stroke-width="2.5" fill="none"/></svg>
                                    </div>
                                    <div class="ppm-title"><?php esc_html_e('Đăng ký nhận ưu đãi', 'whp'); ?></div>
                                    <div class="ppm-sub"><?php esc_html_e('Nhận ngay ưu đãi độc quyền từ chúng tôi', 'whp'); ?></div>
                                    <div class="ppm-field"></div>
                                    <div class="ppm-field"></div>
                                    <div class="ppm-btn"><div class="ppm-btn-text"></div></div>
                                    <div class="ppm-divider"><?php esc_html_e('hoặc', 'whp'); ?></div>
                                    <div class="ppm-socials">
                                        <span style="background:#1877f2;">
                                            <svg width="6" height="6" viewBox="0 0 24 24" fill="#fff"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                                        </span>
                                        <span style="background:#ea4335;">
                                            <svg width="6" height="6" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#fff"/><path d="M12 12h7a7 7 0 1 1-2-5" fill="#ea4335"/></svg>
                                        </span>
                                        <span style="background:#000;">
                                            <svg width="6" height="6" viewBox="0 0 24 24" fill="#fff"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.82a8.18 8.18 0 0 0 4.78 1.53V6.9a4.85 4.85 0 0 1-1-.21z"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-wph-pp-type-info">
                            <div class="mb-wph-pp-type-info-left">
                                <div class="mb-wph-pp-type-icon" style="background: #eff2fe;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="4" width="20" height="16" rx="3" stroke="#3858e9" stroke-width="2"/><path d="M2 8l10 7 10-7" stroke="#3858e9" stroke-width="2" stroke-linecap="round"/></svg>
                                </div>
                                <div class="mb-wph-pp-type-info-text">
                                    <strong>Form / Newsletter</strong>
                                    <span><?php esc_html_e('Thu thập email đăng ký', 'whp'); ?></span>
                                </div>
                            </div>
                            <div class="mb-wph-pp-type-badge"><?php esc_html_e('Chọn', 'whp'); ?></div>
                        </div>
                    </div>

                    <!-- Type 1: Banner / Image -->
                    <div class="mb-wph-pp-type-card <?php echo $whp_popup_type == '1' ? 'selected' : ''; ?>"
                         data-type="1" tabindex="0" role="button">
                        <div class="mb-wph-pp-thumb" style="background: linear-gradient(135deg, #fdf4ff 0%, #ede9fe 100%);">
                            <div class="mb-wph-pp-type-accent" style="background: linear-gradient(90deg, #7c3aed, #c084fc);"></div>
                            <div class="mb-wph-pp-selected-badge">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="mb-wph-pp-thumb-inner">
                                <!-- Simulated website bg -->
                                <div class="ppm-page-bg">
                                    <div class="ppm-page-nav">
                                        <div class="ppm-page-nav-dot" style="background:#ef4444;"></div>
                                        <div class="ppm-page-nav-dot" style="background:#f59e0b;"></div>
                                        <div class="ppm-page-nav-dot" style="background:#22c55e;"></div>
                                    </div>
                                    <div class="ppm-page-body">
                                        <div class="ppm-page-line" style="height:5px;width:55%;"></div>
                                        <div class="ppm-page-line" style="height:4px;width:75%;"></div>
                                        <div class="ppm-page-line" style="height:4px;width:65%;"></div>
                                    </div>
                                </div>
                                <div class="ppm-page-overlay"></div>
                                <!-- Banner modal -->
                                <div class="mb-wph-ppm-banner-modal">
                                    <span class="ppm-close">×</span>
                                    <div class="mb-wph-ppm-banner-img">
                                        <div class="ppm-banner-badge">SALE 50%</div>
                                        <div class="ppm-banner-content">
                                            <div class="ppm-banner-title-line"></div>
                                            <div class="ppm-banner-sub-line"></div>
                                            <div class="ppm-banner-cta"><div class="ppm-banner-cta-text"></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-wph-pp-type-info">
                            <div class="mb-wph-pp-type-info-left">
                                <div class="mb-wph-pp-type-icon" style="background: #f5f3ff;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="18" rx="3" stroke="#8b5cf6" stroke-width="2"/><path d="M2 9h20" stroke="#8b5cf6" stroke-width="2"/><circle cx="7" cy="6" r="1.2" fill="#8b5cf6"/><circle cx="11" cy="6" r="1.2" fill="#c4b5fd"/><circle cx="15" cy="6" r="1.2" fill="#ddd6fe"/><rect x="5" y="13" width="14" height="2" rx="1" fill="#c4b5fd"/><rect x="5" y="17" width="9" height="2" rx="1" fill="#ddd6fe"/></svg>
                                </div>
                                <div class="mb-wph-pp-type-info-text">
                                    <strong><?php esc_html_e('Hình ảnh / Banner', 'whp'); ?></strong>
                                    <span><?php esc_html_e('Ảnh quảng cáo có liên kết', 'whp'); ?></span>
                                </div>
                            </div>
                            <div class="mb-wph-pp-type-badge"><?php esc_html_e('Chọn', 'whp'); ?></div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Settings panel: Form -->
            <div id="mb-wph-pp-panel-form" class="mb-wph-pp-card mb-wph-section-card accent-orange" <?php echo ($whp_popup_type == '1') ? 'style="display:none;"' : ''; ?>>
                <div class="mb-wph-section-header">
                    <div class="mb-wph-section-header-left">
                        <div class="mb-wph-section-icon" style="background:#f97316;">2</div>
                        <div class="mb-wph-section-header-text">
                            <h3><?php esc_html_e('Cài đặt Form Newsletter', 'whp'); ?></h3>
                        </div>
                    </div>
                </div>

                <?php
                $popup_form_source = $whp_popup_form_source ?? 'email';
                $popup_form_id     = $whp_popup_form_id ?? '';

                // Lấy danh sách form từ các plugin phổ biến
                $available_forms = [];
                $form_types = [
                    'wpcf7_contact_form' => 'Contact Form 7',
                    'wpforms'            => 'WPForms',
                    'frm_form'           => 'Formidable',
                    'fluentform'         => 'Fluent Forms',
                ];
                foreach ($form_types as $pt => $label) {
                    if (post_type_exists($pt)) {
                        $forms = get_posts(['post_type' => $pt, 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC']);
                        foreach ($forms as $f) {
                            $available_forms[] = ['id' => $f->ID, 'title' => $f->post_title, 'plugin' => $label, 'type' => $pt];
                        }
                    }
                }
                ?>

                <!-- Sub-selector: nguồn form -->
                <div class="mb-wph-field" style="margin-bottom:20px;">
                    <label style="font-size:13px;font-weight:600;color:#374151;margin-bottom:8px;display:block;"><?php esc_html_e('Kiểu form', 'whp'); ?></label>
                    <div class="mb-wph-pp-source-grid">
                        <div class="mb-wph-pp-source-card <?php echo $popup_form_source !== 'form' ? 'selected' : ''; ?>" data-source="email">
                            <span class="src-icon">✉️</span>
                            <strong><?php esc_html_e('Ô nhập email', 'whp'); ?></strong>
                            <span><?php esc_html_e('Form đơn giản thu thập email đăng ký', 'whp'); ?></span>
                        </div>
                        <div class="mb-wph-pp-source-card <?php echo $popup_form_source === 'form' ? 'selected' : ''; ?>" data-source="form">
                            <span class="src-icon">📋</span>
                            <strong><?php esc_html_e('Form có sẵn', 'whp'); ?></strong>
                            <span><?php esc_html_e('Dùng form từ plugin đang cài trên website', 'whp'); ?></span>
                        </div>
                    </div>
                    <input type="hidden" name="whp_popup_form_source" id="mb_pp_form_source" value="<?php echo esc_attr($popup_form_source); ?>">
                </div>

                <!-- Tiêu đề + phụ: hiển thị cho cả 2 mode -->
                <div class="mb-wph-field">
                    <label><?php echo __('Tiêu đề pop-up', 'whp'); ?></label>
                    <input type="text" class="mb-wph-input" name="whp_popup_title" id="mb_pp_form_title"
                        placeholder="<?php esc_attr_e('Vd: Đăng ký nhận ưu đãi', 'whp'); ?>"
                        value="<?php echo esc_attr($whp_popup_title ?? ''); ?>">
                    <p class="mb-wph-hint"><?php esc_html_e('Tiêu đề chính hiển thị trên pop-up.', 'whp'); ?></p>
                </div>

                <div class="mb-wph-field">
                    <label><?php echo __('Tiêu đề phụ', 'whp'); ?></label>
                    <input type="text" class="mb-wph-input" name="whp_popup_sub_title" id="mb_pp_form_sub"
                        placeholder="<?php esc_attr_e('Vd: Nhận ngay ưu đãi độc quyền từ chúng tôi', 'whp'); ?>"
                        value="<?php echo esc_attr($whp_popup_sub_title ?? ''); ?>">
                    <p class="mb-wph-hint"><?php esc_html_e('Mô tả ngắn bên dưới tiêu đề chính.', 'whp'); ?></p>
                </div>

                <!-- Sub-panel: Form có sẵn — đặt sau tiêu đề/phụ -->
                <div id="mb-pp-source-form-panel" <?php echo $popup_form_source !== 'form' ? 'style="display:none;"' : ''; ?>>
                    <div class="mb-wph-field mb-pp-form-field">
                        <label class="mb-wph-label-icon">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10h8M8 14h5"/></svg>
                            <?php esc_html_e('Chọn form hiển thị', 'whp'); ?>
                        </label>

                        <?php if (!empty($available_forms)) : ?>
                        <div class="mb-pp-form-select-wrap">
                            <select name="whp_popup_form_id" id="mb_pp_form_id"
                                data-forms='<?php echo esc_attr(json_encode(array_map(fn($f) => ['id' => $f['id'], 'title' => $f['title'], 'plugin' => $f['plugin'], 'type' => $f['type']], $available_forms))); ?>'>
                                <option value=""><?php esc_html_e('— Chọn form —', 'whp'); ?></option>
                                <?php foreach ($available_forms as $f) : ?>
                                    <option value="<?php echo esc_attr($f['id']); ?>"
                                        data-plugin="<?php echo esc_attr($f['plugin']); ?>"
                                        data-type="<?php echo esc_attr($f['type']); ?>"
                                        <?php selected($popup_form_id, $f['id']); ?>>
                                        <?php echo esc_html($f['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="mb-pp-form-select-chevron">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="6 9 12 15 18 9"/></svg>
                            </span>
                        </div>

                        <?php
                        $sel_form = $popup_form_id ? array_values(array_filter($available_forms, fn($f) => $f['id'] == $popup_form_id)) : [];
                        $sel = $sel_form[0] ?? null;
                        $badge_map = ['wpcf7_contact_form' => 'cf7', 'wpforms' => 'wpforms', 'frm_form' => 'formidable', 'fluentform' => 'fluent'];
                        ?>
                        <div class="mb-pp-form-info<?php echo $sel ? '' : ' hidden'; ?>" id="mb_pp_form_info">
                            <span class="mb-pp-form-badge <?php echo $sel ? ($badge_map[$sel['type']] ?? 'default') : ''; ?>" id="mb_pp_form_badge">
                                <?php echo esc_html($sel ? $sel['plugin'] : ''); ?>
                            </span>
                            <span class="mb-pp-form-info-name" id="mb_pp_form_info_name"><?php echo esc_html($sel ? $sel['title'] : ''); ?></span>
                            <span class="mb-pp-form-info-id" id="mb_pp_form_info_id"><?php echo $sel ? 'ID: ' . $sel['id'] : ''; ?></span>
                        </div>

                        <?php else : ?>
                        <input type="hidden" name="whp_popup_form_id" value="">
                        <div class="mb-pp-form-empty">
                            <div class="mb-pp-form-empty-icon">⚠️</div>
                            <div class="mb-pp-form-empty-text">
                                <strong><?php esc_html_e('Chưa tìm thấy form nào', 'whp'); ?></strong>
                                <span><?php esc_html_e('Cài Contact Form 7, WPForms, Formidable hoặc Fluent Forms.', 'whp'); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>

                        <p class="mb-wph-hint" style="margin-top:8px;"><?php esc_html_e('Hỗ trợ: Contact Form 7, WPForms, Formidable, Fluent Forms.', 'whp'); ?></p>
                    </div>
                </div>

                <!-- Sub-panel: Chỉ hiện khi Ô nhập email -->
                <div id="mb-pp-source-email-panel" <?php echo $popup_form_source === 'form' ? 'style="display:none;"' : ''; ?>>

                <div class="mb-wph-field">
                    <label><?php echo __('Nút đăng ký', 'whp'); ?></label>
                    <input type="text" class="mb-wph-input" name="whp_popup_button" id="mb_pp_form_btn"
                        placeholder="<?php esc_attr_e('Vd: Đăng ký ngay', 'whp'); ?>"
                        value="<?php echo esc_attr($whp_popup_button ?? ''); ?>">
                    <p class="mb-wph-hint"><?php esc_html_e('Chữ trên nút gửi form.', 'whp'); ?></p>
                </div>

                <!-- Social links -->
                <div class="mb-wph-pp-section-title">
                    <span class="dashicons dashicons-share" style="color:#3858e9;font-size:16px;width:16px;height:16px;"></span>
                    <?php esc_html_e('Liên kết mạng xã hội', 'whp'); ?>
                </div>
                <div class="mb-wph-grid-2">
                    <div class="mb-wph-field" style="margin-bottom:0;">
                        <label class="mb-wph-label-icon">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="#1877F2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            Facebook
                        </label>
                        <input type="text" class="mb-wph-input" name="whp_popup_facebook" id="mb_pp_fb"
                            placeholder="https://facebook.com/..." value="<?php echo esc_attr($whp_popup_facebook ?? ''); ?>">
                    </div>
                    <div class="mb-wph-field" style="margin-bottom:0;">
                        <label class="mb-wph-label-icon">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="#000"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.27 6.27 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.82a8.18 8.18 0 0 0 4.78 1.53V6.9a4.85 4.85 0 0 1-1-.21z"/></svg>
                            TikTok
                        </label>
                        <input type="text" class="mb-wph-input" name="whp_popup_tiktok"
                            placeholder="https://tiktok.com/@..." value="<?php echo esc_attr($whp_popup_tiktok ?? ''); ?>">
                    </div>
                    <div class="mb-wph-field" style="margin-bottom:0;">
                        <label class="mb-wph-label-icon">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="#FF0000"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            YouTube
                        </label>
                        <input type="text" class="mb-wph-input" name="whp_popup_youtube"
                            placeholder="https://youtube.com/..." value="<?php echo esc_attr($whp_popup_youtube ?? ''); ?>">
                    </div>
                    <div class="mb-wph-field" style="margin-bottom:0;">
                        <label class="mb-wph-label-icon">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#E1306C" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                            Instagram
                        </label>
                        <input type="text" class="mb-wph-input" name="whp_popup_instagram"
                            placeholder="https://instagram.com/..." value="<?php echo esc_attr($whp_popup_instagram ?? ''); ?>">
                    </div>
                </div>

                <!-- Email template -->
                <div class="mb-wph-pp-section-title">
                    <span class="dashicons dashicons-email" style="color:#3858e9;font-size:16px;width:16px;height:16px;"></span>
                    <?php esc_html_e('Mẫu email thông báo', 'whp'); ?>
                </div>
                <div class="mb-wph-field" style="margin-bottom:0;">
                    <?php
                    $default_template = '<h5>Chào admin,</h5>
Bạn vừa có người dùng đăng ký nhận tin với email: {email}
<h5>Thân chào</h5>';
                    $mail_template_val = empty($whp_popup_mail_template) ? $default_template : $whp_popup_mail_template;
                    ?>
                    <textarea class="mb-wph-input" name="whp_popup_mail_template"
                              rows="6" style="min-height:120px;font-family:monospace;font-size:13px;"><?php echo esc_textarea($mail_template_val); ?></textarea>
                    <p class="mb-wph-hint" style="margin-top:6px;"><?php esc_html_e('Email tự động gửi khi có người đăng ký. Dùng', 'whp'); ?> <code>{email}</code> <?php esc_html_e('để hiển thị email người đăng ký.', 'whp'); ?></p>
                </div>

                </div><!-- /mb-pp-source-email-panel -->

                </div><!-- /mb-wph-pp-panel-form -->

            <!-- Settings panel: Banner -->
            <div id="mb-wph-pp-panel-banner" class="mb-wph-pp-card" <?php echo ($whp_popup_type != '1') ? 'style="display:none;"' : ''; ?>>
                <h3>
                    <span class="dashicons dashicons-format-image" style="color:#8b5cf6;font-size:17px;width:17px;height:17px;"></span>
                    <?php esc_html_e('Cài đặt Hình ảnh Banner', 'whp'); ?>
                </h3>

                <div class="mb-wph-field">
                    <!-- Upload zone (hiện khi chưa có ảnh) -->
                    <div class="mb-wph-pp-upload-zone" id="mb_pp_upload_zone"
                         style="<?php echo $whp_popup_image_banner ? 'display:none;' : ''; ?>">
                        <div class="mb-wph-pp-upload-zone-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2"><rect x="3" y="3" width="18" height="14" rx="2"/><path d="M3 15l5-5 4 4 3-3 4 4"/><circle cx="8.5" cy="8.5" r="1.5"/></svg>
                        </div>
                        <div class="mb-wph-pp-upload-zone-title"><?php esc_html_e('Nhấn để chọn ảnh từ thư viện', 'whp'); ?></div>
                        <div class="mb-wph-pp-upload-zone-sub"><?php esc_html_e('Hỗ trợ JPG · PNG · WebP · Khuyến nghị 600×400px', 'whp'); ?></div>
                        <button type="button" class="mb-wph-pp-upload-zone-btn" id="mb_pp_open_media">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <?php esc_html_e('Tải ảnh lên', 'whp'); ?>
                        </button>
                    </div>

                    <!-- Preview state (hiện khi có ảnh) -->
                    <div class="mb-wph-pp-img-preview-wrap" id="mb_pp_banner_preview_wrap"
                         style="<?php echo $whp_popup_image_banner ? '' : 'display:none;'; ?>">
                        <img class="mb-wph-pp-img-preview" id="mb_pp_banner_preview"
                             src="<?php echo esc_url($whp_popup_image_banner ?? ''); ?>" alt="Banner">
                        <div class="mb-wph-pp-img-overlay">
                            <button type="button" class="mb-wph-pp-img-action change" id="mb_pp_change_banner">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <?php esc_html_e('Đổi ảnh', 'whp'); ?>
                            </button>
                            <button type="button" class="mb-wph-pp-img-action danger" id="mb_pp_remove_banner">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                                <?php esc_html_e('Xóa ảnh', 'whp'); ?>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="whp_popup_image_banner" id="mb_pp_banner_input"
                           value="<?php echo esc_attr($whp_popup_image_banner ?? ''); ?>">
                </div>

                <div class="mb-wph-field" style="margin-bottom:0;">
                    <label><?php echo __('Link chuyển hướng', 'whp'); ?></label>
                    <input type="text" class="mb-wph-input" name="whp_popup_link_redirect"
                        placeholder="https://example.com/trang-khuyen-mai"
                        value="<?php echo esc_attr($whp_popup_link_redirect ?? ''); ?>">
                    <p class="mb-wph-hint"><?php esc_html_e('URL trang đích khi người dùng nhấp vào banner.', 'whp'); ?></p>
                </div>
            </div>

        </div><!-- /left col -->

        <!-- Right: Preview panel -->
        <div class="mb-wph-pp-preview-panel">
            <div class="mb-wph-pp-preview-card">
                <div class="mb-wph-pp-preview-header" style="display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <h3><?php esc_html_e('Xem trước pop-up', 'whp'); ?></h3>
                        <p><?php esc_html_e('Đây là giao diện pop-up hiển thị trên website', 'whp'); ?></p>
                    </div>
                    <a href="<?php echo esc_url($popup_preview_url); ?>" target="_blank"
                       style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:#fff;border:1.5px solid #fdba74;border-radius:8px;color:#ea580c;font-size:12.5px;font-weight:600;text-decoration:none;white-space:nowrap;flex-shrink:0;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        <?php esc_html_e('Mở tab mới', 'whp'); ?>
                    </a>
                </div>
                <div class="mb-wph-pp-preview-window">
                    <!-- Browser chrome (desktop mode) -->
                    <div class="mb-wph-browser-bar" id="mb_pp_browser_bar">
                        <div class="mb-wph-browser-dots"><span></span><span></span><span></span></div>
                        <div class="mb-wph-browser-url">
                            <svg width="10" height="11" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span><?php echo esc_html(parse_url(get_site_url(), PHP_URL_HOST)); ?></span>
                        </div>
                    </div>
                    <div class="mb-wph-pp-preview-frame-wrap" id="mb_pp_frame_wrap">
                        <iframe id="wpaap_popup_preview_frame"
                                src="<?php echo esc_url($popup_preview_url); ?>"
                                style="width:100%;height:580px;border:none;background:#e5e7eb;display:block;"
                                scrolling="no"></iframe>
                    </div>
                </div>
            </div>

            <!-- Cookie note -->
            <div class="mb-wph-pp-cookie-note">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#22c55e" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                <p><?php echo wp_kses(
                    sprintf(
                        /* translators: 1: cookie name in code tag, 2: opening strong tag, 3: closing strong tag */
                        __('Pop-up chỉ hiển thị với khách chưa đóng (cookie %1$s chưa được set).<br>Để test lại: mở %2$stab ẩn danh%3$s hoặc xóa cookie trong DevTools.', 'whp'),
                        '<code>whp_popup</code>',
                        '<strong>',
                        '</strong>'
                    ),
                    ['code' => [], 'br' => [], 'strong' => []]
                ); ?></p>
            </div>

            <!-- Sidebar: Hướng dẫn -->
            <div class="mb-pp-sidebar-card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="2"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;"><?php esc_html_e('Hướng dẫn', 'whp'); ?></h4>
                        <p style="margin:0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Các bước thiết lập pop-up', 'whp'); ?></p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:0;">
                    <div style="display:flex;gap:12px;padding:9px 0;border-bottom:1px solid #f1f5f9;align-items:center;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#f97316;flex-shrink:0;"></span>
                        <span style="font-size:12px;color:#64748b;flex:1;"><?php esc_html_e('Bật pop-up', 'whp'); ?></span>
                        <span style="font-size:12px;font-weight:700;color:#0f172a;"><?php esc_html_e('Toggle phía trên', 'whp'); ?></span>
                    </div>
                    <div style="display:flex;gap:12px;padding:9px 0;border-bottom:1px solid #f1f5f9;align-items:center;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#3858e9;flex-shrink:0;"></span>
                        <span style="font-size:12px;color:#64748b;flex:1;"><?php esc_html_e('Kiểu hiển thị', 'whp'); ?></span>
                        <span style="font-size:12px;font-weight:700;color:#0f172a;"><?php esc_html_e('Form / Hình ảnh', 'whp'); ?></span>
                    </div>
                    <div style="display:flex;gap:12px;padding:9px 0;border-bottom:1px solid #f1f5f9;align-items:center;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#22c55e;flex-shrink:0;"></span>
                        <span style="font-size:12px;color:#64748b;flex:1;"><?php esc_html_e('Thời gian chờ', 'whp'); ?></span>
                        <span style="font-size:12px;font-weight:700;color:#0f172a;"><?php esc_html_e('Cài ở mục 3', 'whp'); ?></span>
                    </div>
                    <div style="display:flex;gap:12px;padding:9px 0;align-items:center;">
                        <span style="width:8px;height:8px;border-radius:50%;background:#a855f7;flex-shrink:0;"></span>
                        <span style="font-size:12px;color:#64748b;flex:1;"><?php esc_html_e('Reset cookie', 'whp'); ?></span>
                        <span style="font-size:12px;font-weight:700;color:#0f172a;"><?php esc_html_e('Tab ẩn danh', 'whp'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Sidebar: Mẹo tăng chuyển đổi -->
            <div class="mb-pp-sidebar-card">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;"><?php esc_html_e('Mẹo tăng chuyển đổi', 'whp'); ?></h4>
                        <p style="margin:0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Tối ưu hiệu quả pop-up', 'whp'); ?></p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Chờ 3–8 giây', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Để khách đọc nội dung trước — hiện pop-up quá sớm làm tăng tỉ lệ thoát trang.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M4 6h16M4 10h16M4 14h8" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#1e3a8a;display:block;margin-bottom:1px;"><?php esc_html_e('Tiêu đề ngắn & rõ lợi ích', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#2563eb;line-height:1.4;display:block;"><?php esc_html_e('Dòng đầu phải nói ngay lợi ích khách nhận được — tránh câu giới thiệu chung chung.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" stroke="#fff" stroke-width="2.5"/><path d="M14 2v6h6" stroke="#fff" stroke-width="2"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#581c87;display:block;margin-bottom:1px;"><?php esc_html_e('Dùng form sẵn có', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#7c3aed;line-height:1.4;display:block;"><?php esc_html_e('CF7, WPForms tích hợp nhanh — không cần code thêm, dữ liệu lưu thẳng vào plugin.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="2" stroke="#fff" stroke-width="2.5"/><path d="M3 9h18M9 21V9" stroke="#fff" stroke-width="2"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#7c2d12;display:block;margin-bottom:1px;"><?php esc_html_e('Banner phải có nút CTA', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#c2410c;line-height:1.4;display:block;"><?php esc_html_e('Hình ảnh banner cần có nút hành động rõ và link trực tiếp đến trang đích để không lãng phí traffic.', 'whp'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /right col -->

    </div><!-- /layout -->

    <!-- Info cards: delay / flow / cookie — full width below layout -->
    <div id="mb-pp-info-section" class="<?php echo !$whp_popup_active ? 'mb-disabled' : ''; ?>" style="margin-top:0;" <?php echo ($whp_popup_type == '1') ? 'style="display:none;"' : ''; ?>>

        <!-- Card 1: Thời gian chờ -->
        <div class="mb-wph-pp-card mb-wph-section-card accent-purple">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-header-left">
                    <div class="mb-wph-section-icon" style="background:#6366f1;">3</div>
                    <div class="mb-wph-section-header-text">
                        <h3><?php esc_html_e('Thời gian chờ trước khi hiện pop-up', 'whp'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="mb-pp-delay-body-row">
                <div class="mb-pp-delay-left">
                    <div class="mb-pp-delay-input-wrap">
                        <input type="number" class="mb-pp-delay-num" name="whp_popup_delay" id="mb_pp_delay"
                            min="0" max="60" step="1"
                            value="<?php echo esc_attr(intval($whp_popup_delay ?? 8)); ?>">
                        <div class="mb-pp-delay-spinners">
                            <button type="button" class="mb-pp-spin-btn" id="mb_pp_delay_up">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"/></svg>
                            </button>
                            <button type="button" class="mb-pp-spin-btn" id="mb_pp_delay_down">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                        </div>
                    </div>
                    <span class="mb-pp-delay-unit"><?php esc_html_e('giây', 'whp'); ?></span>
                </div>
                <svg class="mb-pp-deco" width="110" height="80" viewBox="0 0 110 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="5" y="5" width="100" height="70" rx="8" fill="#ede9fe" stroke="#c7d2fe" stroke-width="1.5"/>
                    <rect x="5" y="5" width="100" height="18" rx="8" fill="#c7d2fe"/>
                    <rect x="5" y="14" width="100" height="9" fill="#c7d2fe"/>
                    <circle cx="17" cy="14" r="4" fill="#a5b4fc"/>
                    <circle cx="30" cy="14" r="4" fill="#a5b4fc"/>
                    <circle cx="43" cy="14" r="4" fill="#a5b4fc"/>
                    <rect x="20" y="31" width="70" height="6" rx="3" fill="#ddd6fe"/>
                    <rect x="30" y="43" width="50" height="5" rx="2.5" fill="#ddd6fe"/>
                    <rect x="35" y="54" width="40" height="5" rx="2.5" fill="#ddd6fe"/>
                    <circle cx="55" cy="43" r="15" fill="#6366f1" opacity=".15"/>
                    <circle cx="55" cy="43" r="10" fill="#6366f1" opacity=".25"/>
                    <path d="M55 37v6l3.5 2" stroke="#6366f1" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </div>
            <span class="mb-pp-delay-hint" style="display:block; margin-top:12px;"><?php esc_html_e('Thời gian chờ thêm sau khi trang tải xong. 0 = không chờ thêm (hiện ngay khi trang sẵn sàng).', 'whp'); ?></span>
        </div>

        <!-- Card 2: Cách pop-up hoạt động -->
        <div class="mb-wph-pp-card mb-wph-section-card accent-green">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-header-left">
                    <div class="mb-wph-section-icon" style="background:#22c55e;">4</div>
                    <div class="mb-wph-section-header-text">
                        <h3><?php esc_html_e('Cách pop-up hoạt động', 'whp'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="mb-pp-flow-steps-h">
                <div class="mb-pp-flow-step-h">
                    <div class="mb-pp-flow-num">1</div>
                    <div class="mb-pp-flow-icon indigo">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <span class="mb-pp-flow-label"><?php esc_html_e('Khách truy cập website', 'whp'); ?></span>
                </div>
                <div class="mb-pp-flow-arrow-h">→</div>
                <div class="mb-pp-flow-step-h">
                    <div class="mb-pp-flow-num">2</div>
                    <div class="mb-pp-flow-icon green">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </div>
                    <span class="mb-pp-flow-label"><?php esc_html_e('Xem nội dung', 'whp'); ?> <strong id="mb_pp_delay_display"><?php echo intval($whp_popup_delay ?? 8); ?></strong> <?php esc_html_e('giây', 'whp'); ?></span>
                </div>
                <div class="mb-pp-flow-arrow-h">→</div>
                <div class="mb-pp-flow-step-h">
                    <div class="mb-pp-flow-num active">3</div>
                    <div class="mb-pp-flow-icon indigo" style="background:#ede9fe;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <span class="mb-pp-flow-label active"><?php esc_html_e('Pop-up', 'whp'); ?><br><?php esc_html_e('xuất hiện', 'whp'); ?></span>
                </div>
                <div class="mb-pp-flow-arrow-h">→</div>
                <div class="mb-pp-flow-step-h">
                    <div class="mb-pp-flow-num">4</div>
                    <div class="mb-pp-flow-icon red">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </div>
                    <span class="mb-pp-flow-label"><?php esc_html_e('Khách đóng pop-up', 'whp'); ?></span>
                </div>
                <div class="mb-pp-flow-arrow-h">→</div>
                <div class="mb-pp-flow-step-h">
                    <div class="mb-pp-flow-num">5</div>
                    <div class="mb-pp-flow-icon teal">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0d9488" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <span class="mb-pp-flow-label"><?php esc_html_e('Sau 24 giờ', 'whp'); ?><br><?php esc_html_e('mới hiện lại', 'whp'); ?></span>
                </div>
            </div>
        </div>

        <!-- Card 3: Cookie -->
        <div class="mb-wph-pp-card mb-wph-section-card accent-darkblue">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-header-left">
                    <div class="mb-wph-section-icon" style="background:#1d4ed8;">5</div>
                    <div class="mb-wph-section-header-text">
                        <h3><?php esc_html_e('Cookie ghi nhớ', 'whp'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="mb-pp-cookie-body">
                <div>
                    <div class="mb-pp-cookie-row">
                        <span class="mb-pp-cookie-label">Cookie:</span>
                        <span class="mb-pp-cookie-badge">whp_popup</span>
                    </div>
                    <p class="mb-pp-cookie-desc"><?php esc_html_e('Lưu trạng thái hiển thị pop-up.', 'whp'); ?><br><strong><?php esc_html_e('Admin luôn thấy pop-up trên trang chủ để kiểm tra (bỏ qua cookie).', 'whp'); ?></strong></p>
                </div>
                <svg class="mb-pp-deco" width="90" height="70" viewBox="0 0 90 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="4" y="4" width="82" height="56" rx="7" fill="#ede9fe" stroke="#c7d2fe" stroke-width="1.5"/>
                    <rect x="4" y="4" width="82" height="15" rx="7" fill="#c7d2fe"/>
                    <rect x="4" y="11" width="82" height="8" fill="#c7d2fe"/>
                    <circle cx="14" cy="11" r="3" fill="#a5b4fc"/>
                    <circle cx="24" cy="11" r="3" fill="#a5b4fc"/>
                    <circle cx="34" cy="11" r="3" fill="#a5b4fc"/>
                    <circle cx="45" cy="37" r="14" fill="#4f46e5" opacity=".15"/>
                    <path d="M39 37l4 4 8-8" stroke="#4f46e5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                    <circle cx="72" cy="58" r="10" fill="#fde68a" stroke="#fbbf24" stroke-width="1.2"/>
                    <circle cx="68" cy="55" r="1.5" fill="#92400e"/>
                    <circle cx="74" cy="59" r="1.5" fill="#92400e"/>
                    <circle cx="70" cy="61" r="1.5" fill="#92400e"/>
                </svg>
            </div>
            <div class="mb-pp-hint-bar" style="margin-top:16px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#4338ca" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><strong><?php esc_html_e('Gợi ý:', 'whp'); ?></strong> <?php esc_html_e('Nhập 0 để pop-up hiển thị ngay khi trang tải xong, không chờ thêm.', 'whp'); ?></span>
            </div>
        </div>

    </div><!-- /mb-pp-info-section -->

    <!-- Hidden type input -->
    <input type="hidden" name="whp_popup_type" id="mb_pp_type_hidden"
           value="<?php echo esc_attr(($whp_popup_type === '' || $whp_popup_type === null) ? '0' : $whp_popup_type); ?>">

    <!-- Save bar -->
    <div id="mb-popup-save-bar" class="mb-wph-pp-save-bar">
        <span class="mb-wph-pp-save-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            <?php esc_html_e('Các thay đổi sẽ áp dụng ngay sau khi lưu', 'whp'); ?>
        </span>
        <button type="submit" name="submit" class="mb-wph-pp-save-btn">
            <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
            <?php esc_html_e('Lưu thông tin', 'whp'); ?>
        </button>
    </div>

</div><!-- /.mb-wph-pp-wrap -->

<script>
jQuery(document).ready(function($) {

    function whpToast(msg, type) {
        type = type || 'success';
        var wrap = document.getElementById('whp-toast-wrap');
        var t = document.createElement('div');
        t.className = 'whp-toast wt-' + type;
        t.innerHTML = '<div class="whp-toast-icon">' + (type === 'success' ? '✓' : '✕') + '</div><div class="whp-toast-msg">' + msg + '</div><button class="whp-toast-close" onclick="this.parentNode.remove()">×</button>';
        wrap.appendChild(t);
        setTimeout(function() { t.classList.add('wt-out'); setTimeout(function() { t.remove(); }, 280); }, 3000);
    }

    var _ppToggleNonce = '<?php echo esc_js(wp_create_nonce('whp_popup_toggle')); ?>';

    // Toggle label + dim
    function updateToggleState() {
        var isOn = $('#mb_pp_toggle').is(':checked');
        var $label = $('#mb_pp_toggle_label');
        var $layout = $('#mb-popup-layout');
        var $info = $('#mb-pp-info-section');
        var $saveBar = $('#mb-popup-save-bar');
        if (isOn) {
            $label.text('<?php echo esc_js(__('Đang bật', 'whp')); ?>').addClass('active');
            $layout.removeClass('mb-disabled');
            $info.removeClass('mb-disabled');
            $saveBar.css('display', '');
        } else {
            $label.text('<?php echo esc_js(__('Đang tắt', 'whp')); ?>').removeClass('active');
            $layout.addClass('mb-disabled');
            $info.addClass('mb-disabled');
            $saveBar.css('display', 'none');
        }
    }

    $('#mb_pp_toggle').on('change', function() {
        var isOn = $(this).is(':checked');
        updateToggleState();
        var fd = new FormData();
        fd.append('action', 'whp_popup_toggle_enable');
        fd.append('nonce', _ppToggleNonce);
        fd.append('active', isOn ? '1' : '0');
        fetch(ajaxurl, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    whpToast(isOn ? '<?php echo esc_js(__('Đã bật Pop-up', 'whp')); ?>' : '<?php echo esc_js(__('Đã tắt Pop-up', 'whp')); ?>', 'success');
                } else {
                    whpToast('<?php echo esc_js(__('Lỗi khi lưu cài đặt', 'whp')); ?>', 'error');
                }
            })
            .catch(function() { whpToast('<?php echo esc_js(__('Lỗi kết nối', 'whp')); ?>', 'error'); });
    });

    updateToggleState();

    // Delay display sync
    $('#mb_pp_delay').on('input', function() {
        var v = parseInt($(this).val()) || 8;
        $('#mb_pp_delay_display').text(v);
    });

    // Spinner buttons
    $('#mb_pp_delay_up').on('click', function() {
        var $inp = $('#mb_pp_delay');
        var v = Math.min(60, (parseInt($inp.val()) || 0) + 1);
        $inp.val(v).trigger('input');
    });
    $('#mb_pp_delay_down').on('click', function() {
        var $inp = $('#mb_pp_delay');
        var v = Math.max(0, (parseInt($inp.val()) || 0) - 1);
        $inp.val(v).trigger('input');
    });

    // Base preview URL (stripped of old params)
    var basePreviewUrl = $('#wpaap_popup_preview_frame').attr('src').split('&pp_')[0];

    function buildPreviewUrl() {
        var src     = $('#mb_pp_form_source').val() || 'email';
        var fid     = $('select[name="whp_popup_form_id"]').val() || '';
        var title   = encodeURIComponent($('#mb_pp_form_title').val() || '');
        var sub     = encodeURIComponent($('#mb_pp_form_sub').val() || '');
        var btn     = encodeURIComponent($('#mb_pp_form_btn').val() || '');
        var type    = $('#mb_pp_type_hidden').val() || '0';
        var url = basePreviewUrl;
        url += '&pp_type='  + type;
        url += '&pp_fsrc='  + src;
        if (fid)   url += '&pp_fid='   + fid;
        if (title) url += '&pp_title=' + title;
        if (sub)   url += '&pp_sub='   + sub;
        if (btn)   url += '&pp_btn='   + btn;
        return url;
    }

    // Form source card click
    $('.mb-wph-pp-source-card').on('click', function() {
        var src = $(this).data('source');
        $('.mb-wph-pp-source-card').removeClass('selected');
        $(this).addClass('selected');
        $('#mb_pp_form_source').val(src);
        if (src === 'form') {
            $('#mb-pp-source-email-panel').hide();
            $('#mb-pp-source-form-panel').show();
        } else {
            $('#mb-pp-source-form-panel').hide();
            $('#mb-pp-source-email-panel').show();
        }
        reloadPreview();
    });

    // Form selector: update info card + preview on change
    var badgeClassMap = {
        'wpcf7_contact_form': 'cf7',
        'wpforms': 'wpforms',
        'frm_form': 'formidable',
        'fluentform': 'fluent'
    };
    function updateFormInfoCard() {
        var $sel = $('select[name="whp_popup_form_id"]');
        var $opt = $sel.find('option:selected');
        var id   = $sel.val();
        var $info = $('#mb_pp_form_info');
        if (!id) {
            $info.addClass('hidden');
            return;
        }
        var plugin  = $opt.data('plugin') || '';
        var type    = $opt.data('type')   || '';
        var name    = $opt.text().trim();
        var cls     = badgeClassMap[type] || 'default';
        $('#mb_pp_form_badge').attr('class', 'mb-pp-form-badge ' + cls).text(plugin);
        $('#mb_pp_form_info_name').text(name);
        $('#mb_pp_form_info_id').text('ID: ' + id);
        $info.removeClass('hidden');
    }
    updateFormInfoCard();
    $('select[name="whp_popup_form_id"]').on('change', function() {
        updateFormInfoCard();
        reloadPreview();
    });

    // Type card click
    $('.mb-wph-pp-type-card').on('click keydown', function(e) {
        if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
        var type = $(this).data('type');
        switchType(type);
    });

    function switchType(type) {
        // Update cards
        $('.mb-wph-pp-type-card').removeClass('selected');
        $('.mb-wph-pp-type-card[data-type="' + type + '"]').addClass('selected');

        // Update hidden input
        $('#mb_pp_type_hidden').val(type);

        // Show/hide settings panels
        if (type == 0) {
            $('#mb-wph-pp-panel-form').show();
            $('#mb-wph-pp-panel-banner').hide();
            $('#mb-pp-info-section').show();
        } else {
            $('#mb-wph-pp-panel-form').hide();
            $('#mb-wph-pp-panel-banner').show();
            $('#mb-pp-info-section').hide();
        }
        reloadPreview();
    }

    // Reload iframe preview (debounced) với params real-time
    var previewTimer;
    function reloadPreview() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(function() {
            var frame = document.getElementById('wpaap_popup_preview_frame');
            if (frame) frame.src = buildPreviewUrl();
        }, 900);
    }

    // Media frame — tạo 1 lần duy nhất, tái sử dụng
    var bannerMediaFrame = null;

    function openBannerMedia() {
        if (!bannerMediaFrame) {
            bannerMediaFrame = wp.media({
                title: '<?php echo esc_js(__('Chọn hình ảnh Banner', 'whp')); ?>',
                library: { type: 'image' },
                multiple: false,
                button: { text: '<?php echo esc_js(__('Chọn ảnh', 'whp')); ?>' }
            });
            bannerMediaFrame.on('select', function() {
                var attachment = bannerMediaFrame.state().get('selection').first().toJSON();
                var url = attachment.url;
                $('#mb_pp_banner_input').val(url);
                $('#mb_pp_banner_preview').attr('src', url).off('error');
                $('#mb_pp_upload_zone').hide();
                $('#mb_pp_banner_preview_wrap').show();
                reloadPreview();
            });
        }
        bannerMediaFrame.open();
    }

    // Click zone hoặc nút mở media
    $('#mb_pp_upload_zone').on('click', function(e) {
        e.preventDefault();
        openBannerMedia();
    });

    // Nút Đổi ảnh
    $(document).on('click', '#mb_pp_change_banner', function(e) {
        e.preventDefault();
        e.stopPropagation();
        openBannerMedia();
    });

    // Nút Xóa ảnh
    $(document).on('click', '#mb_pp_remove_banner', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#mb_pp_banner_input').val('');
        $('#mb_pp_banner_preview').attr('src', '');
        $('#mb_pp_banner_preview_wrap').hide();
        $('#mb_pp_upload_zone').show();
        reloadPreview();
    });
});
</script>

</form>

<?php whp_get_shared('footer'); ?>
