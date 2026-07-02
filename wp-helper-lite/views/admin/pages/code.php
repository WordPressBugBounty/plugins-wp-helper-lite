<?php if (!defined('ABSPATH')) exit; ?>
<?php whp_get_shared('header'); ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
.mb-wph-page {
    font-family: inherit;
    max-width: 1200px;
    margin: 20px auto 40px;
    padding: 0 20px 40px;
    box-sizing: border-box;
}

/* Header card */
.mb-wph-header-card {
    position: relative;
    background: linear-gradient(100deg, #ffffff 0%, #f5f3ff 45%, #ede9fe 100%);
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(99,102,241,0.1), 0 0 0 1px #e0d9ff;
    margin-bottom: 22px;
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
.mb-wph-header-title-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.mb-wph-header-icon-box {
    width: 44px; height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #6366f1, #4f46e5);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99,102,241,0.35);
}
.mb-wph-page-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 0;
}
.mb-wph-page-header h1 {
    font-size: 24px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.4px;
}
.mb-wph-page-subtitle {
    color: #64748b;
    font-size: 13.5px;
    line-height: 1.6;
    margin: 0;
    padding-left: 58px;
    max-width: 400px;
}

/* Cards */
.mb-wph-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 20px;
    overflow: hidden;
}
.mb-wph-card-inner { padding: 22px 24px; }

.mb-wph-section-card { border-left: 4px solid transparent; }
.mb-wph-section-card.accent-blue   { border-left-color: #3858e9; }
.mb-wph-section-card.accent-orange { border-left-color: #f97316; }
.mb-wph-section-card.accent-purple { border-left-color: #8b5cf6; }

/* Section header */
.mb-wph-section-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-section-header-left {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}
.mb-wph-section-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.accent-blue   .mb-wph-section-icon { background: #eff2fe; }
.accent-orange .mb-wph-section-icon { background: #fff4ed; }
.accent-purple .mb-wph-section-icon { background: #f5f3ff; }

.mb-wph-section-header-text h3 {
    margin: 0 0 4px 0;
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.mb-wph-section-header-text p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
}

/* Code tag badge */
.mb-badge {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    padding: 1px 7px;
    font-size: 11.5px;
    font-family: 'Courier New', monospace;
    color: #3858e9;
    font-weight: 700;
    letter-spacing: 0.2px;
}

/* Info badge (right side) */
.mb-code-info-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 12px;
    color: #64748b;
    white-space: nowrap;
    flex-shrink: 0;
    line-height: 1.5;
}
.mb-code-info-badge code {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 11.5px;
}
@media (max-width: 680px) {
    .mb-code-info-badge { display: none; }
    .mb-wph-section-header { flex-direction: column; }
}

/* Code editor with line numbers */
.mb-code-editor-wrap {
    display: flex;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    overflow: hidden;
    background: #f8fafc;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.mb-code-editor-wrap:focus-within {
    border-color: #3858e9;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.1);
}
.mb-code-linenums {
    display: flex;
    flex-direction: column;
    padding: 14px 10px 14px 14px;
    background: #f1f5f9;
    border-right: 1px solid #e2e8f0;
    min-width: 38px;
    text-align: right;
    user-select: none;
    flex-shrink: 0;
    overflow: hidden;
}
.mb-code-linenums span {
    display: block;
    font-size: 12px;
    color: #94a3b8;
    font-family: 'Courier New', monospace;
    line-height: 22px;
}
.mb-code-editor-wrap textarea {
    flex: 1;
    min-height: 132px;
    padding: 14px 16px;
    background: transparent;
    color: #1e293b;
    border: none;
    font-family: 'JetBrains Mono', 'Fira Code', 'Courier New', monospace;
    font-size: 13px;
    line-height: 22px;
    resize: vertical;
    box-sizing: border-box;
    outline: none;
    tab-size: 2;
    caret-color: #3858e9;
    width: 100%;
}
.mb-code-editor-wrap textarea::placeholder {
    color: #94a3b8;
    font-style: italic;
}

/* Two-column layout */
.mb-code-layout-wrapper {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1024px) {
    .mb-code-layout-wrapper { grid-template-columns: 1fr; }
}
.mb-code-sidebar-col { position: sticky; top: 32px; }

/* Save bar */
.mb-wph-save-bar {
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
.mb-wph-save-note {
    font-size: 12.5px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}
.mb-wph-save-btn {
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
.mb-wph-save-btn:hover {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(56,88,233,.4);
}
</style>

<form method="post" id="mb-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-header-left">
            <div class="mb-wph-header-title-row">
                <div class="mb-wph-header-icon-box">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M8 9l-3 3 3 3M16 9l3 3-3 3M13 7l-2 10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;">Header &amp; Footer Code</h1>
            </div>
            <p class="mb-wph-page-subtitle"><?php echo wp_kses_post(__($itemInfo['desc'] ?? 'Chèn mã tùy chỉnh vào phần head, body hoặc footer của trang web. Tính năng Header &amp; Footer giúp bạn làm mọi thứ đơn giản hơn, chỉ cần sao chép và dán đoạn code vào đoạn script phù hợp là hoàn tất.', 'whp')); ?></p>
        </div>
        <div class="mb-wph-header-right">
            <svg viewBox="0 0 680 168" fill="none" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;position:absolute;inset:0;">
                <defs>
                    <linearGradient id="cbg" x1="0" y1="0" x2="1" y2="0">
                        <stop offset="0%" stop-color="#f5f3ff" stop-opacity="0"/>
                        <stop offset="25%" stop-color="#ede9fe" stop-opacity="0.5"/>
                        <stop offset="100%" stop-color="#ddd6fe" stop-opacity="1"/>
                    </linearGradient>
                    <linearGradient id="editorBg" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#1e1b4b"/>
                        <stop offset="100%" stop-color="#1e293b"/>
                    </linearGradient>
                    <linearGradient id="browserBg" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#f8fafc"/>
                        <stop offset="100%" stop-color="#f1f5f9"/>
                    </linearGradient>
                    <filter id="cs" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="4" stdDeviation="6" flood-color="rgba(99,102,241,0.2)"/>
                    </filter>
                    <filter id="cssm" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="3" stdDeviation="4" flood-color="rgba(99,102,241,0.18)"/>
                    </filter>
                </defs>
                <rect width="680" height="168" fill="url(#cbg)"/>

                <!-- Rocket/arrow top-right deco -->
                <g transform="translate(628,12) rotate(-30)" filter="url(#cssm)">
                    <path d="M8 20 L16 0 L24 20 L16 15 Z" fill="#a78bfa" opacity="0.8"/>
                    <path d="M13 20 L16 15 L19 20" fill="#7c3aed" opacity="0.6"/>
                </g>
                <!-- Deco dots -->
                <circle cx="545" cy="14" r="5"   fill="rgba(139,92,246,0.3)"/>
                <circle cx="560" cy="7"  r="3.5" fill="rgba(99,102,241,0.22)"/>
                <circle cx="532" cy="22" r="3"   fill="rgba(167,139,250,0.2)"/>

                <!-- ══ Code editor window ══ -->
                <rect x="370" y="16" width="210" height="138" rx="12" fill="url(#editorBg)" filter="url(#cs)"/>
                <!-- Title bar -->
                <rect x="370" y="16" width="210" height="30" rx="12" fill="#2d2766"/>
                <rect x="370" y="34" width="210" height="12" fill="#2d2766"/>
                <!-- Traffic lights -->
                <circle cx="386" cy="31" r="5" fill="#ef4444"/>
                <circle cx="401" cy="31" r="5" fill="#f59e0b"/>
                <circle cx="416" cy="31" r="5" fill="#22c55e"/>
                <rect x="432" y="27" width="60" height="7" rx="3.5" fill="rgba(255,255,255,0.12)"/>
                <!-- Line numbers -->
                <rect x="375" y="52" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="375" y="64" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="375" y="76" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="375" y="88" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="375" y="100" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="375" y="112" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="375" y="124" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <rect x="375" y="136" width="12" height="5" rx="2.5" fill="rgba(255,255,255,0.15)"/>
                <!-- Code syntax lines -->
                <rect x="393" y="52" width="38" height="5" rx="2.5" fill="#f97316" opacity=".9"/>
                <rect x="435" y="52" width="22" height="5" rx="2.5" fill="#a78bfa" opacity=".85"/>
                <rect x="461" y="52" width="18" height="5" rx="2.5" fill="#34d399" opacity=".8"/>
                <rect x="401" y="64" width="55" height="5" rx="2.5" fill="#818cf8" opacity=".9"/>
                <rect x="460" y="64" width="28" height="5" rx="2.5" fill="#fbbf24" opacity=".8"/>
                <rect x="401" y="76" width="44" height="5" rx="2.5" fill="#34d399" opacity=".85"/>
                <rect x="449" y="76" width="35" height="5" rx="2.5" fill="#f97316" opacity=".75"/>
                <rect x="393" y="88" width="30" height="5" rx="2.5" fill="#f97316" opacity=".9"/>
                <rect x="401" y="100" width="62" height="5" rx="2.5" fill="#a78bfa" opacity=".85"/>
                <rect x="401" y="112" width="48" height="5" rx="2.5" fill="#34d399" opacity=".8"/>
                <rect x="453" y="112" width="24" height="5" rx="2.5" fill="#818cf8" opacity=".75"/>
                <rect x="393" y="124" width="36" height="5" rx="2.5" fill="#f97316" opacity=".9"/>
                <rect x="401" y="136" width="70" height="5" rx="2.5" fill="#818cf8" opacity=".7"/>

                <!-- ══ Browser window (behind, left of editor) ══ -->
                <rect x="285" y="38" width="150" height="108" rx="10" fill="url(#browserBg)" filter="url(#cs)"/>
                <rect x="285" y="38" width="150" height="26" rx="10" fill="#f8fafc"/>
                <rect x="285" y="52" width="150" height="12" fill="#f1f5f9"/>
                <circle cx="297" cy="51" r="4" fill="#ef4444" opacity="0.7"/>
                <circle cx="308" cy="51" r="4" fill="#f59e0b" opacity="0.7"/>
                <circle cx="319" cy="51" r="4" fill="#22c55e" opacity="0.7"/>
                <rect x="328" y="46" width="70" height="10" rx="5" fill="#e2e8f0"/>
                <rect x="332" y="49" width="55" height="4" rx="2" fill="#cbd5e1"/>
                <!-- Browser content lines -->
                <rect x="295" y="72" width="110" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="295" y="84" width="130" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="295" y="96" width="90" height="7" rx="3.5" fill="#e2e8f0"/>
                <rect x="295" y="110" width="120" height="20" rx="6" fill="#6366f1" opacity="0.8"/>
                <rect x="316" y="117" width="78" height="6" rx="3" fill="white" opacity="0.85"/>

                <!-- ── Dotted connections ── -->
                <line x1="252" y1="84" x2="285" y2="84" stroke="#a78bfa" stroke-width="2" stroke-dasharray="5,5" opacity="0.7"/>
                <line x1="285" y1="84" x2="285" y2="55"  stroke="#a78bfa" stroke-width="2" stroke-dasharray="5,5" opacity="0.6"/>
                <line x1="252" y1="84" x2="218" y2="48"  stroke="#a78bfa" stroke-width="2" stroke-dasharray="5,5" opacity="0.6"/>
                <line x1="218" y1="48" x2="178" y2="34"  stroke="#a78bfa" stroke-width="2" stroke-dasharray="5,5" opacity="0.5"/>
                <line x1="252" y1="84" x2="210" y2="116" stroke="#a78bfa" stroke-width="2" stroke-dasharray="5,5" opacity="0.55"/>
                <line x1="252" y1="84" x2="172" y2="84"  stroke="#a78bfa" stroke-width="2" stroke-dasharray="5,5" opacity="0.5"/>

                <!-- ── Center icon: code bracket ── -->
                <circle cx="252" cy="84" r="42" fill="white" filter="url(#cs)"/>
                <circle cx="252" cy="84" r="34" fill="#f5f3ff"/>
                <path d="M238 72 L228 84 L238 96" stroke="#6366f1" stroke-width="4.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M266 72 L276 84 L266 96" stroke="#6366f1" stroke-width="4.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="248" y1="70" x2="256" y2="98" stroke="#a78bfa" stroke-width="3.5" stroke-linecap="round" opacity="0.85"/>

                <!-- ── Floating badge: HTML ── -->
                <rect x="154" y="18" width="54" height="34" rx="10" fill="#f97316" filter="url(#cssm)"/>
                <text x="181" y="40" text-anchor="middle" fill="white" font-family="'Courier New',monospace" font-size="12" font-weight="bold">HTML</text>

                <!-- ── Floating badge: CSS ── -->
                <rect x="348" y="8" width="48" height="34" rx="10" fill="#3b82f6" filter="url(#cssm)"/>
                <text x="372" y="30" text-anchor="middle" fill="white" font-family="'Courier New',monospace" font-size="12" font-weight="bold">CSS</text>

                <!-- ── Floating badge: JS (bottom-left) ── -->
                <rect x="173" y="106" width="48" height="34" rx="10" fill="#f59e0b" filter="url(#cssm)"/>
                <text x="197" y="128" text-anchor="middle" fill="white" font-family="'Courier New',monospace" font-size="13" font-weight="bold">JS</text>

                <!-- ── Small node ── -->
                <circle cx="158" cy="84" r="16" fill="white" filter="url(#cssm)"/>
                <circle cx="158" cy="84" r="11" fill="#ede9fe"/>
                <circle cx="158" cy="84" r="6"  fill="#a78bfa"/>

                <!-- ── Plant ── -->
                <rect x="633" y="148" width="7" height="18" rx="3.5" fill="#4ade80" opacity="0.9"/>
                <ellipse cx="628" cy="144" rx="14" ry="8"  fill="#22c55e" opacity="0.95" transform="rotate(-30 628 144)"/>
                <ellipse cx="644" cy="141" rx="12" ry="7"  fill="#4ade80" opacity="0.85" transform="rotate(22 644 141)"/>
                <ellipse cx="636" cy="134" rx="9"  ry="5.5" fill="#86efac" opacity="0.9"  transform="rotate(-8 636 134)"/>
                <rect x="630" y="154" width="7" height="14" rx="3.5" fill="#15803d" opacity="0.55"/>
            </svg>
        </div>
    </div>

    <!-- Two-column layout -->
    <div class="mb-code-layout-wrapper">

    <!-- Left: Editor cards -->
    <div class="mb-code-main-col">

    <!-- Card 1: Header Scripts -->
    <div class="mb-wph-card mb-wph-section-card accent-blue">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-header-left">
                    <div class="mb-wph-section-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <div class="mb-wph-section-header-text">
                        <h3>Header Scripts <span class="mb-badge">&lt;head&gt;</span></h3>
                        <p><?php esc_html_e( 'Đoạn mã sẽ được chèn vào thẻ', 'whp' ); ?> <code>&lt;head&gt;</code> <?php esc_html_e( 'của trang.', 'whp' ); ?><br><?php esc_html_e( 'Phù hợp cho Google Analytics, Facebook Pixel, meta tags, CSS inline.', 'whp' ); ?></p>
                    </div>
                </div>
                <div class="mb-code-info-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    <?php esc_html_e( 'Mã được chèn ngay trước thẻ đóng', 'whp' ); ?> <code style="color:#3858e9;">&lt;/head&gt;</code>
                </div>
            </div>
            <div class="mb-code-editor-wrap">
                <div class="mb-code-linenums" id="ln_header"></div>
                <textarea name="whp_code_header" id="ta_header" placeholder="<?php echo esc_attr( __( '<!-- Nhập mã HTML, <script> hoặc <style> ở đây -->', 'whp' ) ); ?>"><?php echo esc_textarea( wp_unslash( $whp_code_header ) ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Card 2: Body Scripts Top -->
    <div class="mb-wph-card mb-wph-section-card accent-orange">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-header-left">
                    <div class="mb-wph-section-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                    </div>
                    <div class="mb-wph-section-header-text">
                        <h3>Body Scripts – Top <span class="mb-badge" style="color:#f97316;">&lt;body&gt;</span></h3>
                        <p><?php esc_html_e( 'Đoạn mã sẽ được chèn ngay sau thẻ mở', 'whp' ); ?> <code>&lt;body&gt;</code>.<br><?php esc_html_e( 'Phù hợp cho Google Tag Manager noscript, chat widgets cần tải sớm.', 'whp' ); ?></p>
                    </div>
                </div>
                <div class="mb-code-info-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    <?php esc_html_e( 'Mã được chèn ngay sau thẻ mở', 'whp' ); ?> <code style="color:#f97316;">&lt;body&gt;</code>
                </div>
            </div>
            <div class="mb-code-editor-wrap">
                <div class="mb-code-linenums" id="ln_body"></div>
                <textarea name="whp_code_body" id="ta_body" placeholder="<?php echo esc_attr( __( '<!-- Nhập mã HTML hoặc <noscript> ở đây -->', 'whp' ) ); ?>"><?php echo esc_textarea( wp_unslash( $whp_code_body ) ); ?></textarea>
            </div>
        </div>
    </div>

    <!-- Card 3: Footer Scripts -->
    <div class="mb-wph-card mb-wph-section-card accent-purple">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-header-left">
                    <div class="mb-wph-section-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"/><line x1="12" y1="19" x2="20" y2="19"/></svg>
                    </div>
                    <div class="mb-wph-section-header-text">
                        <h3>Footer Scripts <span class="mb-badge" style="color:#8b5cf6;">&lt;/body&gt;</span></h3>
                        <p><?php esc_html_e( 'Đoạn mã sẽ được chèn ngay trước thẻ đóng', 'whp' ); ?> <code>&lt;/body&gt;</code>.<br><?php esc_html_e( 'Phù hợp cho script phân tích, remarketing, live chat cần tải sau cùng.', 'whp' ); ?></p>
                    </div>
                </div>
                <div class="mb-code-info-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    <?php esc_html_e( 'Mã được chèn ngay trước thẻ đóng', 'whp' ); ?> <code style="color:#8b5cf6;">&lt;/body&gt;</code>
                </div>
            </div>
            <div class="mb-code-editor-wrap">
                <div class="mb-code-linenums" id="ln_footer"></div>
                <textarea name="whp_code_footer" id="ta_footer" placeholder="<?php echo esc_attr( __( '<!-- Nhập mã <script> hoặc HTML ở đây -->', 'whp' ) ); ?>"><?php echo esc_textarea( wp_unslash( $whp_code_footer ) ); ?></textarea>
            </div>
        </div>
    </div>

    </div><!-- /.mb-code-main-col -->

    <!-- Right: Sidebar tips -->
    <div class="mb-code-sidebar-col">

        <!-- Vị trí chèn code -->
        <div class="mb-wph-card" style="margin-bottom: 16px;">
            <div class="mb-wph-card-inner" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                    <div style="width: 30px; height: 30px; border-radius: 8px; background: linear-gradient(135deg, #6366f1, #4f46e5); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M8 9l-3 3 3 3M16 9l3 3-3 3M13 7l-2 10" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <h3 style="margin: 0; font-size: 13.5px; font-weight: 700; color: #0f172a;"><?php esc_html_e( 'Vị trí chèn code', 'whp' ); ?></h3>
                </div>
                <div style="display: flex; flex-direction: column; gap: 0;">
                    <div style="display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="width: 28px; height: 28px; border-radius: 6px; background: #eff2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 10px; font-weight: 800; color: #3858e9; font-family: monospace;">&lt;H&gt;</span>
                        <div>
                            <strong style="font-size: 12px; color: #0f172a; display: block; margin-bottom: 2px;">Header <code style="font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; color: #3858e9;">&lt;/head&gt;</code></strong>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.5; display: block;"><?php esc_html_e( 'Google Analytics 4, Facebook Pixel, meta tags tùy chỉnh, CSS inline.', 'whp' ); ?></span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f5f9;">
                        <span style="width: 28px; height: 28px; border-radius: 6px; background: #fff4ed; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 10px; font-weight: 800; color: #f97316; font-family: monospace;">&lt;B&gt;</span>
                        <div>
                            <strong style="font-size: 12px; color: #0f172a; display: block; margin-bottom: 2px;">Body Top <code style="font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; color: #f97316;">&lt;body&gt;</code></strong>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.5; display: block;"><?php esc_html_e( 'Google Tag Manager noscript, widget cần tải trước nội dung trang.', 'whp' ); ?></span>
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; padding: 10px 0;">
                        <span style="width: 28px; height: 28px; border-radius: 6px; background: #f5f3ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 10px; font-weight: 800; color: #8b5cf6; font-family: monospace;">&lt;F&gt;</span>
                        <div>
                            <strong style="font-size: 12px; color: #0f172a; display: block; margin-bottom: 2px;">Footer <code style="font-size: 11px; background: #f1f5f9; padding: 1px 5px; border-radius: 4px; color: #8b5cf6;">&lt;/body&gt;</code></strong>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.5; display: block;"><?php esc_html_e( 'Script analytics, remarketing, live chat cần tải sau cùng để không chặn render.', 'whp' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tips card -->
        <div class="mb-wph-card">
            <div class="mb-wph-card-inner" style="padding: 18px 20px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;">
                    <div style="width: 30px; height: 30px; border-radius: 8px; background: linear-gradient(135deg, #f59e0b, #fbbf24); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(245,158,11,0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h3 style="margin: 0 0 1px; font-size: 13.5px; font-weight: 700; color: #0f172a;"><?php esc_html_e( 'Mẹo sử dụng', 'whp' ); ?></h3>
                        <p style="margin: 0; font-size: 11.5px; color: #94a3b8;"><?php esc_html_e( 'Chèn code hiệu quả và an toàn', 'whp' ); ?></p>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">

                    <div style="display: flex; gap: 10px; padding: 10px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; align-items: flex-start;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #22c55e; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size: 12px; color: #166534; display: block; margin-bottom: 1px;"><?php esc_html_e( 'Bọc script trong thẻ đúng', 'whp' ); ?></strong>
                            <span style="font-size: 11.5px; color: #16a34a; line-height: 1.4; display: block;"><?php esc_html_e( 'JavaScript phải nằm trong', 'whp' ); ?> <code style="background:#dcfce7;padding:1px 4px;border-radius:3px;">&lt;script&gt;</code>, CSS <?php esc_html_e( 'trong', 'whp' ); ?> <code style="background:#dcfce7;padding:1px 4px;border-radius:3px;">&lt;style&gt;</code>.</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; padding: 10px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; align-items: flex-start;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="3" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size: 12px; color: #1e3a8a; display: block; margin-bottom: 1px;"><?php esc_html_e( 'Script nặng → đặt Footer', 'whp' ); ?></strong>
                            <span style="font-size: 11.5px; color: #2563eb; line-height: 1.4; display: block;"><?php esc_html_e( 'Đặt script phân tích/chat ở Footer để tránh làm chậm tốc độ tải trang (Core Web Vitals).', 'whp' ); ?></span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; padding: 10px 12px; background: #fdf4ff; border: 1px solid #e9d5ff; border-radius: 8px; align-items: flex-start;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #a855f7; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="5" fill="#fff"/></svg>
                        </span>
                        <div>
                            <strong style="font-size: 12px; color: #581c87; display: block; margin-bottom: 1px;">Google Tag Manager → Body Top</strong>
                            <span style="font-size: 11.5px; color: #7c3aed; line-height: 1.4; display: block;"><?php esc_html_e( 'Đoạn noscript của GTM phải đặt ngay sau', 'whp' ); ?> <code style="background:#faf5ff;padding:1px 4px;border-radius:3px;">&lt;body&gt;</code> <?php esc_html_e( 'để đúng spec của Google.', 'whp' ); ?></span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; padding: 10px 12px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; align-items: flex-start;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #f97316; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="#fff" stroke-width="2"/></svg>
                        </span>
                        <div>
                            <strong style="font-size: 12px; color: #7c2d12; display: block; margin-bottom: 1px;"><?php esc_html_e( 'Kiểm tra trước khi lưu', 'whp' ); ?></strong>
                            <span style="font-size: 11.5px; color: #c2410c; line-height: 1.4; display: block;"><?php esc_html_e( 'Code sai cú pháp có thể làm trắng trang. Validate JS/HTML bằng công cụ trước khi dán vào.', 'whp' ); ?></span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; padding: 10px 12px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; align-items: flex-start;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #16a34a; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size: 12px; color: #166534; display: block; margin-bottom: 1px;"><?php esc_html_e( 'Mỗi ô chỉ nên 1 mục đích', 'whp' ); ?></strong>
                            <span style="font-size: 11.5px; color: #15803d; line-height: 1.4; display: block;"><?php esc_html_e( 'Không trộn lẫn tracking, CSS và widget trong cùng 1 ô. Dễ debug và quản lý hơn về sau.', 'whp' ); ?></span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 10px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; align-items: flex-start;">
                        <span style="width: 18px; height: 18px; border-radius: 50%; background: #64748b; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#fff" stroke-width="2.5"/><path d="M8 21h8M12 17v4" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size: 12px; color: #374151; display: block; margin-bottom: 1px;"><?php esc_html_e( 'Dùng comment để ghi chú', 'whp' ); ?></strong>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4; display: block;"><?php esc_html_e( 'Thêm', 'whp' ); ?> <code style="background:#f1f5f9;padding:1px 4px;border-radius:3px;">&lt;!-- tên công cụ --&gt;</code> <?php esc_html_e( 'trước mỗi đoạn code để dễ nhận biết sau này.', 'whp' ); ?></span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div><!-- /.mb-code-sidebar-col -->

    </div><!-- /.mb-code-layout-wrapper -->

    <!-- Save bar — full width outside grid -->
    <div class="mb-wph-save-bar">
        <span class="mb-wph-save-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="vertical-align:middle;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            <?php esc_html_e( 'Các thay đổi sẽ được áp dụng ngay sau khi lưu.', 'whp' ); ?>
        </span>
        <button type="submit" name="submit" class="mb-wph-save-btn">
            <span class="dashicons dashicons-saved" style="font-size:15px;width:15px;height:15px;line-height:15px;"></span>
            <?php esc_html_e( 'Lưu thông tin', 'whp' ); ?>
        </button>
    </div>

</div><!-- /.mb-wph-page -->
</form>

<script>
(function() {
    var editors = [
        { ta: document.getElementById('ta_header'), ln: document.getElementById('ln_header') },
        { ta: document.getElementById('ta_body'),   ln: document.getElementById('ln_body')   },
        { ta: document.getElementById('ta_footer'), ln: document.getElementById('ln_footer') }
    ];

    function updateLineNums(ta, ln) {
        if (!ta || !ln) return;
        var lines = Math.max(ta.value.split('\n').length, 6);
        var html = '';
        for (var i = 1; i <= lines; i++) html += '<span>' + i + '</span>';
        ln.innerHTML = html;
        ln.scrollTop = ta.scrollTop;
    }

    editors.forEach(function(e) {
        updateLineNums(e.ta, e.ln);
        if (!e.ta) return;
        e.ta.addEventListener('input',  function() { updateLineNums(e.ta, e.ln); });
        e.ta.addEventListener('scroll', function() { e.ln.scrollTop = e.ta.scrollTop; });
        e.ta.addEventListener('keydown', function(ev) {
            if (ev.key === 'Tab') {
                ev.preventDefault();
                var s = e.ta.selectionStart, end = e.ta.selectionEnd;
                e.ta.value = e.ta.value.substring(0, s) + '  ' + e.ta.value.substring(end);
                e.ta.selectionStart = e.ta.selectionEnd = s + 2;
                updateLineNums(e.ta, e.ln);
            }
        });
    });
})();
</script>

<?php whp_get_shared('footer'); ?>
