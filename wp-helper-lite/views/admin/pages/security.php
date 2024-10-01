<?php whp_get_shared('header'); ?>
<?php if ($isSubmit == 1) : ?>
    <div class="whp-notify">
        <?php echo __('Cập nhật cài đặt thành công', 'whp'); ?>
    </div>
<?php endif; ?>
<div class="whp-desc">

    <?php echo whp_show_html(__($itemInfo['desc'] ?? "", 'whp')) ?>
</div>
<form method="post">
    <?php wp_nonce_field('_token', '_token'); ?>
    <div class="whp-setting" id="security-setting">
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Vô hiệu hóa XML-RPC', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_security_remove_xmlrpc" class="setting-value" value="<?php echo esc_attr($whp_security_remove_xmlrpc); ?>" name="whp_security_remove_xmlrpc" <?php echo esc_attr($whp_security_remove_xmlrpc_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Việc tính năng này được tắt đi đồng nghĩa với việc sẽ hạn chế các cuộc tấn công như: dò mật khẩu và làm quá tải hệ thống không xử lý được yêu cầu thực sự của khách hàng.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Cấm sao chép nội dung', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_security_disable_copy" class="setting-value" value="<?php echo esc_attr($whp_security_disable_copy); ?>" name="whp_security_disable_copy" <?php echo esc_attr($whp_security_disable_copy_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <?php echo __('<p>Khách hàng của bạn sẽ không thể nhấp phải vào trang để xem hoặc sao chép mã code trên trang web của bạn.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Xóa các liên kết từ wp_head', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_security_delete_wphead" class="setting-value" value="<?php echo esc_attr($whp_security_delete_wphead); ?>" name="whp_security_delete_wphead" <?php echo esc_attr($whp_security_delete_wphead_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <?php echo __('<p>Sử dụng tính năng này sẽ giúp xóa đi các liên kết ở <code>&lt;head&gt;</code> giúp trang web được tải nhanh hơn, giúp SEO hiệu quả hơn.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Ẩn phiên bản WordPress', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_security_hide_wp_version" class="setting-value" value="<?php echo esc_attr($whp_security_hide_wp_version); ?>" name="whp_security_hide_wp_version" <?php echo esc_attr($whp_security_hide_wp_version_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <?php echo __('<p>Ẩn thông tin phiên bản WordPress khỏi cấu trúc DOM(HTML) của website.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Ẩn menu theme / plugin', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_security_hide_theme_plugin" class="setting-value" value="<?php echo esc_attr($whp_security_hide_theme_plugin); ?>" name="whp_security_hide_theme_plugin" <?php echo esc_attr($whp_security_hide_theme_plugin_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <?php echo __('<p>Ẩn menu theme / plugin, tắt chức năng chỉnh sửa theme và plugin.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Thay đổi đường dẫn đăng nhập', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_security_change_login_url" class="setting-value" value="<?php echo esc_attr($whp_security_change_login_url); ?>" name="whp_security_change_login_url" <?php echo esc_attr($whp_security_change_login_url_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <?php echo __('<p>Việc thay đổi này sẽ tránh các cuộc tấn công dò mật khẩu và còn giúp bạn tự tạo đường dẫn dễ nhớ và thuận tiện cho bạn hơn.</p>', 'whp'); ?>
                        <div class="input-group <?php echo esc_attr($whp_security_change_login_url_check) ?>">
                            <span><?php echo esc_html(get_site_url()); ?>/</span>
                            <input type="text" class="form-control  " placeholder="<?php echo __('VD: dangnhap', 'whp'); ?>" id="new_login_url" name="whp_new_login_url" value="<?php echo esc_attr($whp_new_login_url); ?>">
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="whp-buttons">
        <?php submit_button(__('Lưu thông tin', 'whp')); ?>
    </div>
</form>
<?php whp_get_shared('footer'); ?>