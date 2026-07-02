<?php if (!defined('ABSPATH')) exit; ?>
<?php whp_get_shared('header'); ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
/* ==============================
   Tiện ích mở rộng - Modern Layout (mb-)
   ============================== */
.mb-wph-page {
    font-family: inherit;
    max-width: 1080px;
    margin: 20px auto 40px;
    padding: 0 15px 40px;
    box-sizing: border-box;
}
.mb-ext-layout { display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
@media (max-width: 820px) { .mb-ext-layout { grid-template-columns: 1fr; } .mb-ext-sidebar { display: none; } }
.mb-ext-sidebar { position: sticky; top: 32px; }
.mb-ext-tips-card {
    background: #fff; border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    overflow: hidden; margin-bottom: 16px;
}
.mb-ext-tips-inner { padding: 18px 20px; }
.mb-wph-header-card {
    background: #fff;
    padding: 24px 30px;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 25px;
    border-left: 5px solid #3858e9;
}
.mb-wph-page-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}
.mb-wph-page-header h1 {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.mb-wph-page-subtitle {
    color: #475569;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
    padding-left: 38px;
}
.mb-wph-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    margin-bottom: 20px;
    overflow: hidden;
}
.mb-wph-card-inner { padding: 24px; }
.mb-wph-section-card {
    border-left: 4px solid transparent;
    transition: box-shadow 0.2s;
}
.mb-wph-section-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1), 0 0 0 1px #e8edf3;
}
.mb-wph-section-card.accent-blue   { border-left-color: #3858e9; }
.mb-wph-section-card.accent-orange { border-left-color: #f97316; }
.mb-wph-section-card.accent-purple { border-left-color: #8b5cf6; }
.mb-wph-section-card.accent-green  { border-left-color: #22c55e; }
.mb-wph-section-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-section-icon {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.accent-blue   .mb-wph-section-icon { background: #eff2fe; }
.accent-orange .mb-wph-section-icon { background: #fff4ed; }
.accent-purple .mb-wph-section-icon { background: #f5f3ff; }
.accent-green  .mb-wph-section-icon { background: #f0fdf4; }
.mb-wph-section-header-text h3 {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}
.mb-wph-section-header-text p {
    margin: 0;
    font-size: 13.5px;
    color: #475569;
    line-height: 1.6;
}

/* Toggle row */
.mb-wph-toggle-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 16px 0;
    border-bottom: 1px solid #f1f5f9;
}
.mb-wph-toggle-row:last-child { border-bottom: none; }
.mb-wph-toggle-info { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.mb-wph-toggle-name { font-size: 15px; font-weight: 600; color: #0f172a; }
.mb-wph-toggle-desc { font-size: 13px; color: #64748b; line-height: 1.5; }
.mb-wph-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
    margin-top: 2px;
}
.mb-wph-switch input { display: none; }
.mb-wph-slider {
    position: absolute;
    inset: 0;
    background: #cbd5e1;
    border-radius: 28px;
    cursor: pointer;
    transition: background 0.25s;
}
.mb-wph-slider::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    left: 4px;
    top: 4px;
    transition: transform 0.25s;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.mb-wph-switch input:checked + .mb-wph-slider { background: #22c55e; }
.mb-wph-switch input:checked + .mb-wph-slider::after { transform: translateX(24px); }

/* Form fields */
.mb-wph-field { margin-bottom: 0; }
.mb-wph-field > label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
}
.mb-wph-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13.5px;
    color: #1e293b;
    background: #f8fafc;
    box-sizing: border-box;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.mb-wph-select:focus {
    border-color: #3858e9;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.12);
}
.mb-wph-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13.5px;
    color: #1e293b;
    background: #f8fafc;
    box-sizing: border-box;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    margin-top: 4px;
}
.mb-wph-input:focus {
    border-color: #3858e9;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.12);
}
.mb-wph-hint {
    margin: 6px 0 0;
    font-size: 12.5px;
    color: #94a3b8;
    line-height: 1.5;
}

/* Login theme extra fields */
.mb-login-extra {
    display: none;
    flex-direction: column;
    gap: 12px;
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px dashed #e2e8f0;
    width: 100%;
}
.mb-login-extra.is-visible { display: flex; }

/* Image upload area */
.mb-wph-upload-area {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.mb-wph-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    background: #f1f5f9;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}
.mb-wph-upload-btn:hover {
    background: #e2e8f0;
    border-color: #94a3b8;
}

/* Save bar */
.mb-wph-save-bar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
}
.mb-wph-save-note { font-size: 12.5px; color: #64748b; }
.mb-wph-save-btn {
    background: linear-gradient(135deg, #3858e9 0%, #2563eb 100%);
    color: #fff;
    border: none;
    border-radius: 9px;
    padding: 11px 32px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(56,88,233,0.35);
    transition: all 0.2s;
}
.mb-wph-save-btn:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(56,88,233,0.4);
}

@media (max-width: 600px) {
    .mb-wph-header-card { padding: 18px; }
    .mb-wph-card-inner { padding: 16px; }
    .mb-wph-save-bar { flex-direction: column; gap: 12px; align-items: stretch; }
    .mb-wph-save-btn { width: 100%; text-align: center; }
}
</style>

<form method="post" id="mb-extention-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-page-header">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                <circle cx="12" cy="12" r="12" fill="#eff2fe"/>
                <path d="M20.5 11H19V7c0-1.1-.9-2-2-2h-4V3.5A2.5 2.5 0 0 0 10.5 1 2.5 2.5 0 0 0 8 3.5V5H4C2.9 5 2 5.9 2 7v3.8h1.5c1.5 0 2.7 1.2 2.7 2.7 0 1.5-1.2 2.7-2.7 2.7H2V20c0 1.1.9 2 2 2h3.8v-1.5c0-1.5 1.2-2.7 2.7-2.7 1.5 0 2.7 1.2 2.7 2.7V22H17c1.1 0 2-.9 2-2v-4h1.5A2.5 2.5 0 0 0 23 13.5 2.5 2.5 0 0 0 20.5 11z" fill="#3858e9"/>
            </svg>
            <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Tiện ích mở rộng', 'whp'); ?></h1>
        </div>
        <p class="mb-wph-page-subtitle"><?php echo wp_kses_post(__($itemInfo['desc'] ?? 'Bật/tắt các tiện ích mở rộng giúp nâng cao tính năng và hiệu năng website.', 'whp')); ?></p>
    </div>

    <div class="mb-ext-layout">
    <div class="mb-ext-main">

    <!-- Card 1: Trình soạn thảo -->
    <div class="mb-wph-card mb-wph-section-card accent-blue">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                </div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Trình soạn thảo', 'whp'); ?></h3>
                    <p><?php esc_html_e('Chọn loại trình soạn thảo mặc định cho trang và bài viết.', 'whp'); ?></p>
                </div>
            </div>
            <div class="mb-wph-field">
                <label><?php echo __('Trình soạn thảo văn bản', 'whp'); ?></label>
                <select class="mb-wph-select" name="whp_extention_editor_type">
                    <?php
                    $listEditor = $data['listEditor'] ?? [];
                    foreach ($listEditor as $itemList) :
                        $itemValue  = $itemList['value'] ?? '';
                        $itemName   = $itemList['name']  ?? '';
                        $itemSelect = ($whp_extention_editor_type == $itemValue) ? 'selected' : '';
                    ?>
                        <option value="<?php echo esc_attr($itemValue); ?>" <?php echo esc_attr($itemSelect); ?>>
                            <?php echo esc_html($itemName); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mb-wph-hint"><?php esc_html_e('Chọn Classic Editor nếu bạn quen với giao diện cũ, Block Editor (Gutenberg) cho giao diện mới.', 'whp'); ?></p>
            </div>
        </div>
    </div>

    <!-- Card 2: Nội dung -->
    <div class="mb-wph-card mb-wph-section-card accent-orange">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f97316" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Nội dung', 'whp'); ?></h3>
                    <p><?php esc_html_e('Các tính năng hỗ trợ quản lý nội dung trang và bài viết.', 'whp'); ?></p>
                </div>
            </div>

            <!-- Nhân bản trang/bài viết -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Nhân bản trang / bài viết', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Cho phép tạo bản sao của trang hoặc bài viết với một cú nhấp chuột.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_duplicate_page_post"
                        value="<?php echo esc_attr($whp_extention_duplicate_page_post); ?>"
                        <?php echo esc_attr($whp_extention_duplicate_page_post_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- Nhân bản menu -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Nhân bản menu', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Tạo bản sao của menu điều hướng hiện có để tái sử dụng và chỉnh sửa dễ dàng hơn.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_duplicate_menu"
                        value="<?php echo esc_attr($whp_extention_duplicate_menu); ?>"
                        <?php echo esc_attr($whp_extention_duplicate_menu_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- 404 redirect -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Chuyển 404 về trang chủ', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Tự động chuyển hướng về trang chủ khi khách truy cập gặp lỗi 404 (trang không tìm thấy).', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_enable_404_redirect"
                        value="<?php echo esc_attr($whp_extention_enable_404_redirect); ?>"
                        <?php echo esc_attr($whp_extention_enable_404_redirect_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

        </div>
    </div>

    <!-- Card 3: Hiệu năng -->
    <div class="mb-wph-card mb-wph-section-card accent-purple">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8b5cf6" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Hiệu năng', 'whp'); ?></h3>
                    <p><?php esc_html_e('Tắt các tài nguyên không cần thiết để tăng tốc độ tải trang.', 'whp'); ?></p>
                </div>
            </div>

            <!-- Emojis -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Xóa biểu tượng Emojis', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Không tải file', 'whp'); ?> <code>wp-emoji-release.min.js</code> — <?php esc_html_e('giảm số request HTTP không cần thiết.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_disable_emojis"
                        value="<?php echo esc_attr($whp_extention_disable_emojis); ?>"
                        <?php echo esc_attr($whp_extention_disable_emojis_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- Query strings -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name">Remove Query Strings</span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Xóa chuỗi truy vấn (?ver=x.x) khỏi tài nguyên tĩnh để tối ưu cache trình duyệt.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_remove_query_string"
                        value="<?php echo esc_attr($whp_extention_remove_query_string); ?>"
                        <?php echo esc_attr($whp_extention_remove_query_string_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- Embeds -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name">Disable WordPress Embeds</span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Tắt tính năng chèn mã nhúng oEmbeds trong WordPress để giảm tải không cần thiết.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_disbale_wp_embeds"
                        value="<?php echo esc_attr($whp_extention_disbale_wp_embeds); ?>"
                        <?php echo esc_attr($whp_extention_disbale_wp_embeds_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- Google Fonts -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Tắt Google Font', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Không tải Google Fonts từ server bên ngoài — dùng font mặc định của hệ thống thay thế.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_disbale_google_fonts"
                        value="<?php echo esc_attr($whp_extention_disbale_google_fonts); ?>"
                        <?php echo esc_attr($whp_extention_disbale_google_fonts_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

        </div>
    </div>

    <!-- Card 4: Quản trị -->
    <div class="mb-wph-card mb-wph-section-card accent-green">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Quản trị', 'whp'); ?></h3>
                    <p><?php esc_html_e('Tùy chỉnh giao diện và tính năng trong trang quản trị WordPress.', 'whp'); ?></p>
                </div>
            </div>

            <!-- Tắt thông báo -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Tắt thông báo', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Ẩn tất cả thông báo cập nhật và admin notices trong trang quản trị.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_notification"
                        value="<?php echo esc_attr($whp_extention_notification); ?>"
                        <?php echo esc_attr($whp_extention_notification); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- Tắt Dashicons -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Tắt Dashicons', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Không tải Dashicons cho người dùng chưa đăng nhập — giảm tải CSS không cần thiết.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_disbale_dashicons"
                        value="<?php echo esc_attr($whp_extention_disbale_dashicons); ?>"
                        <?php echo esc_attr($whp_extention_disbale_dashicons_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- Giao diện đăng nhập -->
            <div class="mb-wph-toggle-row" style="flex-wrap: wrap;">
                <div class="mb-wph-toggle-info" style="flex-basis: calc(100% - 72px);">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Giao diện đăng nhập', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Thay logo WordPress mặc định bằng logo của bạn trên trang đăng nhập.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_custom_login_theme"
                        id="wpg_login_theme_toggle"
                        value="<?php echo esc_attr($whp_extention_custom_login_theme); ?>"
                        <?php echo esc_attr($whp_extention_custom_login_theme_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
                <!-- Extra fields khi bật -->
                <div class="mb-login-extra <?php echo ($whp_extention_custom_login_theme_check === 'checked') ? 'is-visible' : ''; ?>"
                    id="wpg_login_theme_extra">
                    <div>
                        <label style="display:block;font-size:13.5px;font-weight:600;color:#374151;margin-bottom:8px;"><?php esc_html_e('Logo trang đăng nhập', 'whp'); ?></label>
                        <div class="mb-wph-upload-area">
                            <button class="mb-wph-upload-btn btn-upload-logo" type="button" id="uploadLogo">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Logo
                            </button>
                            <div class="preview-group" style="position:relative;display:inline-block;">
                                <img src="<?php echo esc_url(MB_WHP_URL . '/assets/admin/images/remove.png'); ?>"
                                    class="preview-close img-responsive"
                                    style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;cursor:pointer;background:#fee2e2;border-radius:50%;padding:2px;"
                                    data-default="<?php echo esc_url(MB_WHP_URL . '/assets/admin/images/placeholder-image.jpg'); ?>">
                                <img src="<?php echo esc_url($whp_extention_custom_login_logo); ?>"
                                    class="preview-logo img-responsive"
                                    style="max-width:120px;max-height:60px;border-radius:6px;border:1px solid #e2e8f0;object-fit:contain;">
                            </div>
                        </div>
                        <input type="hidden" name="whp_extention_custom_login_logo" value="<?php echo esc_attr($whp_extention_custom_login_logo); ?>">
                    </div>
                    <div>
                        <label style="display:block;font-size:13.5px;font-weight:600;color:#374151;margin-bottom:7px;"><?php esc_html_e('Đường dẫn liên kết khi nhấp logo', 'whp'); ?></label>
                        <input type="text" class="mb-wph-input" name="whp_extention_custom_link"
                            placeholder="<?php esc_attr_e('Nhập đường dẫn tùy biến, ví dụ: https://matbao.net', 'whp'); ?>"
                            value="<?php echo esc_attr($whp_extention_custom_link); ?>">
                    </div>
                </div>
            </div>

            <!-- Upload SVG -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Cho phép upload SVG', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Bật hỗ trợ upload file SVG vào thư viện media của WordPress.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_extention_svg"
                        value="<?php echo esc_attr($whp_extention_svg); ?>"
                        <?php echo esc_attr($whp_extention_svg_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

        </div>
    </div>

    </div><!-- /mb-ext-main -->

    <!-- Sidebar -->
    <div class="mb-ext-sidebar">

        <!-- Mẹo sử dụng -->
        <div class="mb-ext-tips-card">
            <div class="mb-ext-tips-inner">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                    <div style="width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(245,158,11,0.25);">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2a7 7 0 0 1 7 7c0 2.5-1.3 4.7-3.3 6l-.7 3H9l-.7-3A7 7 0 0 1 5 9a7 7 0 0 1 7-7z" fill="#fff"/><path d="M9 22h6" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <div>
                        <h3 style="margin:0 0 1px;font-size:13.5px;font-weight:700;color:#0f172a;"><?php esc_html_e('Mẹo sử dụng', 'whp'); ?></h3>
                        <p style="margin:0;font-size:11.5px;color:#94a3b8;"><?php esc_html_e('Bật tiện ích hiệu quả & an toàn', 'whp'); ?></p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Chỉ bật những gì cần thiết', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#16a34a;line-height:1.4;display:block;"><?php esc_html_e('Mỗi tiện ích bật thêm đều ảnh hưởng hiệu năng. Bật có chọn lọc để website chạy nhanh hơn.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#3b82f6;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" stroke="#fff" stroke-width="2.5" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#1e3a8a;display:block;margin-bottom:1px;">Classic Editor &amp; Page Builder</strong>
                            <span style="font-size:11.5px;color:#2563eb;line-height:1.4;display:block;"><?php esc_html_e('Bật Classic Editor nếu đang dùng Flatsome, Elementor hoặc quen giao diện soạn thảo cũ.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#581c87;display:block;margin-bottom:1px;"><?php esc_html_e('Tắt Emojis & Embeds', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#7c3aed;line-height:1.4;display:block;"><?php esc_html_e('Giảm request HTTP không cần thiết — cải thiện điểm PageSpeed và Core Web Vitals đáng kể.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#f97316;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#7c2d12;display:block;margin-bottom:1px;"><?php esc_html_e('Nhân bản & Chuyển hướng 404', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#c2410c;line-height:1.4;display:block;"><?php esc_html_e('Nhân bản trang giúp tạo nhanh từ template. Chuyển 404 về trang chủ tránh mất khách vô ích.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#16a34a;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><path d="M20 6L9 17l-5-5" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#166534;display:block;margin-bottom:1px;"><?php esc_html_e('Kiểm tra sau khi bật tiện ích mới', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#15803d;line-height:1.4;display:block;"><?php esc_html_e('Luôn xem thử trang sau khi thay đổi để phát hiện xung đột plugin hoặc lỗi giao diện sớm.', 'whp'); ?></span>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;align-items:flex-start;">
                        <span style="width:18px;height:18px;border-radius:50%;background:#64748b;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none"><rect x="2" y="3" width="20" height="14" rx="2" stroke="#fff" stroke-width="2.5"/><path d="M8 21h8M12 17v4" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <div>
                            <strong style="font-size:12px;color:#374151;display:block;margin-bottom:1px;"><?php esc_html_e('Giới hạn Revision để nhẹ database', 'whp'); ?></strong>
                            <span style="font-size:11.5px;color:#64748b;line-height:1.4;display:block;"><?php esc_html_e('Bật giới hạn revision giúp database gọn hơn, truy vấn nhanh hơn — đặc biệt trên site có nhiều bài viết.', 'whp'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /mb-ext-sidebar -->

    </div><!-- /mb-ext-layout -->

    <!-- Save bar -->
    <div class="mb-wph-save-bar">
        <span class="mb-wph-save-note">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="#94a3b8" style="vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg>
            <?php esc_html_e('Các thay đổi sẽ áp dụng ngay sau khi lưu', 'whp'); ?>
        </span>
        <button type="submit" name="submit" class="mb-wph-save-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:middle;margin-right:6px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?php esc_html_e('Lưu thông tin', 'whp'); ?>
        </button>
    </div>

</div><!-- /.mb-wph-page -->
</form>

<script>
(function() {
    'use strict';

    // Giao diện đăng nhập: hiện/ẩn logo + link fields
    var loginThemeToggle = document.getElementById('wpg_login_theme_toggle');
    var loginThemeExtra  = document.getElementById('wpg_login_theme_extra');

    if (loginThemeToggle && loginThemeExtra) {
        function updateLoginTheme() {
            if (loginThemeToggle.checked) {
                loginThemeExtra.classList.add('is-visible');
                loginThemeExtra.style.display = 'flex';
            } else {
                loginThemeExtra.classList.remove('is-visible');
                loginThemeExtra.style.display = 'none';
            }
        }
        loginThemeToggle.addEventListener('change', updateLoginTheme);
        updateLoginTheme();
    }
})();
</script>

<?php whp_get_shared('footer'); ?>
