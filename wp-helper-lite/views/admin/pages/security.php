<?php if (!defined('ABSPATH')) exit; ?>
<?php whp_get_shared('header'); ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
/* ==============================
   Bảo mật - Modern Layout (mb-)
   ============================== */
.mb-wph-page {
    font-family: inherit;
    max-width: 800px;
    margin: 20px auto 40px;
    padding: 0 15px 40px;
    box-sizing: border-box;
}
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
.mb-wph-section-card.accent-blue { border-left-color: #3858e9; }
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
.accent-blue .mb-wph-section-icon { background: #eff2fe; }
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

/* Login URL extra input */
.mb-login-url-row {
    display: none;
    margin-top: 12px;
}
.mb-login-url-row.is-visible { display: flex; }
.mb-url-prefix {
    display: inline-flex;
    align-items: center;
    padding: 10px 12px;
    background: #f1f5f9;
    border: 1px solid #d1d5db;
    border-right: none;
    border-radius: 8px 0 0 8px;
    font-size: 12.5px;
    color: #64748b;
    white-space: nowrap;
    max-width: 220px;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mb-url-input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #d1d5db;
    border-radius: 0 8px 8px 0;
    font-size: 13.5px;
    color: #1e293b;
    background: #f8fafc;
    box-sizing: border-box;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    min-width: 0;
}
.mb-url-input:focus {
    border-color: #3858e9;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(56,88,233,0.12);
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
    .mb-url-prefix { max-width: 130px; font-size: 11px; }
    .mb-wph-save-bar { flex-direction: column; gap: 12px; align-items: stretch; }
    .mb-wph-save-btn { width: 100%; text-align: center; }
}
</style>

<form method="post" id="mb-security-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-page-header">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                <circle cx="12" cy="12" r="12" fill="#eff2fe"/>
                <path d="M12 3L4 7v5c0 4.4 3.4 8.5 8 9.5 4.6-1 8-5.1 8-9.5V7L12 3z" fill="#3858e9"/>
                <path d="M9 12l2 2 4-4" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Bảo mật', 'whp'); ?></h1>
        </div>
        <p class="mb-wph-page-subtitle"><?php echo wp_kses_post(__($itemInfo['desc'] ?? 'Cài đặt các tính năng bảo mật giúp bảo vệ website WordPress của bạn.', 'whp')); ?></p>
    </div>

    <!-- Security toggles card -->
    <div class="mb-wph-card mb-wph-section-card accent-blue">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-section-header">
                <div class="mb-wph-section-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3858e9" stroke-width="2"><path d="M12 3L4 7v5c0 4.4 3.4 8.5 8 9.5 4.6-1 8-5.1 8-9.5V7L12 3z"/></svg>
                </div>
                <div class="mb-wph-section-header-text">
                    <h3><?php esc_html_e('Cài đặt bảo mật', 'whp'); ?></h3>
                    <p><?php esc_html_e('Bật/tắt các tính năng bảo mật để tăng cường bảo vệ website của bạn.', 'whp'); ?></p>
                </div>
            </div>

            <!-- 1. XML-RPC -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Vô hiệu hóa XML-RPC', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Hạn chế các cuộc tấn công dò mật khẩu và làm quá tải hệ thống qua giao thức XML-RPC.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_security_remove_xmlrpc" value="1"
                        <?php echo esc_attr($whp_security_remove_xmlrpc_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- 2. Cấm sao chép -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Cấm sao chép nội dung', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Tắt chuột phải và xem mã nguồn trang — khách truy cập không thể sao chép nội dung.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_security_disable_copy" value="1"
                        <?php echo esc_attr($whp_security_disable_copy_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- 3. Xóa wp_head links -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Xóa các liên kết từ wp_head', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php echo wp_kses_post(__('Xóa các liên kết không cần thiết trong <code>&lt;head&gt;</code> giúp tải nhanh hơn và SEO tốt hơn.', 'whp')); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_security_delete_wphead" value="1"
                        <?php echo esc_attr($whp_security_delete_wphead_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- 4. Ẩn WP version -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Ẩn phiên bản WordPress', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Ẩn thông tin phiên bản WordPress khỏi mã HTML của website, tránh lộ thông tin cho hacker.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_security_hide_wp_version" value="1"
                        <?php echo esc_attr($whp_security_hide_wp_version_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- 5. Ẩn menu theme/plugin -->
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Ẩn menu Theme / Plugin', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Tắt chức năng chỉnh sửa theme và plugin trực tiếp trong trang quản trị WordPress.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_security_hide_theme_plugin" value="1"
                        <?php echo esc_attr($whp_security_hide_theme_plugin_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
            </div>

            <!-- 6. Thay đổi login URL -->
            <div class="mb-wph-toggle-row" style="flex-wrap: wrap;">
                <div class="mb-wph-toggle-info" style="flex-basis: calc(100% - 72px);">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Thay đổi đường dẫn đăng nhập', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Thay URL /wp-login.php bằng đường dẫn tùy chỉnh để tránh tấn công dò mật khẩu.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" name="whp_security_change_login_url"
                        id="wpg_login_url_toggle"
                        value="1"
                        <?php echo esc_attr($whp_security_change_login_url_check); ?>>
                    <span class="mb-wph-slider"></span>
                </label>
                <!-- Login URL input row (hiện khi bật) -->
                <div class="mb-login-url-row <?php echo ($whp_security_change_login_url_check === 'checked') ? 'is-visible' : ''; ?>"
                    id="wpg_login_url_field" style="width:100%; margin-top:12px;">
                    <span class="mb-url-prefix"><?php echo esc_html(get_site_url()); ?>/</span>
                    <input type="text" class="mb-url-input" id="new_login_url" name="whp_new_login_url"
                        placeholder="<?php esc_attr_e('Ví dụ: dangnhap', 'whp'); ?>"
                        value="<?php echo esc_attr($whp_new_login_url); ?>">
                </div>
            </div>

        </div>
    </div>

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

    var loginUrlToggle = document.getElementById('wpg_login_url_toggle');
    var loginUrlField  = document.getElementById('wpg_login_url_field');

    if (loginUrlToggle && loginUrlField) {
        function updateLoginUrlField() {
            if (loginUrlToggle.checked) {
                loginUrlField.classList.add('is-visible');
                loginUrlField.style.display = 'flex';
            } else {
                loginUrlField.classList.remove('is-visible');
                loginUrlField.style.display = 'none';
            }
        }
        loginUrlToggle.addEventListener('change', updateLoginUrlField);
        updateLoginUrlField();
    }
})();
</script>

<?php whp_get_shared('footer'); ?>
