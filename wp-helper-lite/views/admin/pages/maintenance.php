<?php if (!defined('ABSPATH')) exit; ?>
<?php whp_get_shared('header'); ?>

<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify"><?php echo __('Cập nhật cài đặt thành công', 'whp'); ?></div>
<?php endif; ?>

<style>
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
.mb-wph-toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}
.mb-wph-toggle-info { display: flex; flex-direction: column; gap: 4px; }
.mb-wph-toggle-name { font-size: 15px; font-weight: 600; color: #0f172a; }
.mb-wph-toggle-desc { font-size: 13px; color: #64748b; line-height: 1.5; }
.mb-wph-switch {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
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
.mb-wph-save-bar {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.07), 0 0 0 1px #e8edf3;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
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
    display: inline-flex;
    align-items: center;
    gap: 7px;
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
    .mb-wph-save-btn { width: 100%; text-align: center; justify-content: center; }
}
</style>

<form method="post" id="mb-maintenance-form">
<?php wp_nonce_field('_token', '_token'); ?>

<div class="mb-wph-page">

    <!-- Page Header -->
    <div class="mb-wph-header-card">
        <div class="mb-wph-page-header">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;">
                <circle cx="12" cy="12" r="12" fill="#eff2fe"/>
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3-3a5.9 5.9 0 0 1-7.6 7.6L6 19.8a2.1 2.1 0 0 1-3-3l6.9-6.9a5.9 5.9 0 0 1 7.6-7.6l-3 3z" fill="#3858e9"/>
            </svg>
            <h1 style="font-size:24px;font-weight:700;color:#0f172a;margin:0;letter-spacing:-0.4px;"><?php esc_html_e('Chế độ bảo trì', 'whp'); ?></h1>
        </div>
        <p class="mb-wph-page-subtitle"><?php echo wp_kses_post(__($itemInfo['desc'] ?? 'Bật chế độ bảo trì để hiển thị trang thông báo cho khách truy cập khi website đang nâng cấp.', 'whp')); ?></p>
    </div>

    <!-- Toggle card -->
    <div class="mb-wph-card">
        <div class="mb-wph-card-inner">
            <div class="mb-wph-toggle-row">
                <div class="mb-wph-toggle-info">
                    <span class="mb-wph-toggle-name"><?php esc_html_e('Kích hoạt chế độ bảo trì', 'whp'); ?></span>
                    <span class="mb-wph-toggle-desc"><?php esc_html_e('Khi bật, khách truy cập sẽ thấy trang bảo trì thay vì nội dung website.', 'whp'); ?></span>
                </div>
                <label class="mb-wph-switch">
                    <input type="checkbox" id="enable_maintenance" name="whp_maintenance_active"
                        value="1" <?php echo $whp_maintenance_active_check; ?>>
                    <span class="mb-wph-slider"></span>
                </label>
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
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            <?php esc_html_e('Lưu thông tin', 'whp'); ?>
        </button>
    </div>

</div>
</form>

<?php whp_get_shared('footer'); ?>
