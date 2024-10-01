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
                    <label for=""><?php echo esc_html(__('Trình soạn thảo văn bản', 'whp')); ?></label>
                    <select name="whp_extention_editor_type" id="" class="form-control">
                        <?php
                        $listEditor = $data['listEditor'] ?? [];
                        foreach ($listEditor as $itemList) :
                            $itemValue = $itemList['value'] ?? "";
                            $itemName = $itemList['name'] ?? "";
                            $itemSelect = $whp_extention_editor_type == $itemValue ? "selected" : "";
                        ?>
                            <option value="<?php echo esc_attr($itemValue); ?>" <?php echo esc_attr($itemSelect); ?>>
                                <?php echo esc_html($itemName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Nhân bản trang/ bài viết', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_duplicate_page_post" class="setting-value" value="<?php echo esc_attr($whp_extention_duplicate_page_post); ?>" name="whp_extention_duplicate_page_post" <?php echo esc_attr($whp_extention_duplicate_page_post_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Cho phép nhân bản trang / bài viết.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Nhân bản menu', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_duplicate_menu" class="setting-value" value="<?php echo esc_attr($whp_extention_duplicate_menu); ?>" name="whp_extention_duplicate_menu" <?php echo esc_attr($whp_extention_duplicate_menu_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Tính năng nhân bản trang/ bài viết và menu sẽcho phép bạn có thểtạo thêm bản sao giống với nội dung đã được tạo. Việc này sẽ giúp bạn thao tác nhanh hơn trong việc tạo trang WordPress của mình.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Chuyển 404 về trang chủ', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_enable_404_redirect" class="setting-value" value="<?php echo esc_attr($whp_extention_enable_404_redirect); ?>" name="whp_extention_enable_404_redirect" <?php echo esc_attr($whp_extention_enable_404_redirect_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Lỗi 404 là lỗi thường gặp ở các trang web. Tính năng này sẽ <strong>giúp chuyển hướng truy cập về lại trang chủ</strong> của bạn, khi khách hàng gặp phải các liên kết bị lỗi.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Xóa biểu tượng Emojis', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_disable_emojis" class="setting-value" value="<?php echo esc_attr($whp_extention_disable_emojis); ?>" name="whp_extention_disable_emojis" <?php echo esc_attr($whp_extention_disable_emojis_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Không load tệp <strong>wp-emoji-release.min.js</strong> chứa các icon cảm xúc của WordPress.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Remove Query Strings', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_remove_query_string" class="setting-value" value="<?php echo esc_attr($whp_extention_remove_query_string); ?>" name="whp_extention_remove_query_string" <?php echo esc_attr($whp_extention_remove_query_string_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Xóa chuỗi truy vấn khỏi tài nguyên tĩnh', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Disable Wordpress Embeds', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_disbale_wp_embeds" class="setting-value" value="<?php echo esc_attr($whp_extention_disbale_wp_embeds); ?>" name="whp_extention_disbale_wp_embeds" <?php echo esc_attr($whp_extention_disbale_wp_embeds_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Tắt tính năng chèn <strong>mã nhúng oEmbeds</strong> trong WordPress', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Tắt Google Font', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_disbale_google_fonts" class="setting-value" value="<?php echo esc_attr($whp_extention_disbale_google_fonts); ?>" name="whp_extention_disbale_google_fonts" <?php echo esc_attr($whp_extention_disbale_google_fonts_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Tắt không load <strong>Google font</strong> trên trang, và load font mặc định của trang', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Tắt thông báo ', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_notification" class="setting-value" value="<?php echo esc_attr($whp_extention_notification); ?>" name="whp_extention_notification" <?php echo esc_attr($whp_extention_notification) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Tính năng này sẽ tắt thông báo trong trang quản trị.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Tắt Dashicons', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_disbale_dashicons" class="setting-value" value="<?php echo esc_attr($whp_extention_disbale_dashicons); ?>" name="whp_extention_disbale_dashicons" <?php echo esc_attr($whp_extention_disbale_dashicons_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Tắt dashicons trên giao diện người dùng khi chưa đăng nhập.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Giao diện đăng nhập', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_custom_login_theme" class="setting-value" value="<?php echo esc_attr($whp_extention_custom_login_theme); ?>" name="whp_extention_custom_login_theme" <?php echo esc_attr($whp_extention_custom_login_theme_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Bạn đã có thể <strong>sử dụng logo của mình</strong> để thay cho logo mặc định <strong>wordpress</strong> sở trang đăng nhập và <strong>liên kết khi nhấp</strong> vào logo đó.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item <?php echo esc_attr($whp_extention_custom_login_theme_check); ?>" id="custom_login">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Logo', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <button class="btn btn-upload-logo " type="button" id="uploadLogo"><?php echo __('Upload Logo', 'whp'); ?></button>
                        <div class="preview-group">
                            <img src="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/remove.png"); ?>" class="img-responsive preview-close" data-default="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/placeholder-image.jpg"); ?>">
                            <img src="<?php echo esc_url($whp_extention_custom_login_logo); ?>" class="img-responsive preview-logo">
                        </div>
                        <input type="hidden" name="whp_extention_custom_login_logo" value="<?php echo esc_attr($whp_extention_custom_login_logo); ?>">
                    </div>
                </div>
            </div>
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Đường dẫn liên kết', 'whp')); ?>
                    </label>
                    <input type="text" class="form-control" name="whp_extention_custom_link" placeholder="<?php echo __('Nhập đường dẫn tùy biến', 'whp'); ?>" value="<?php echo esc_attr($whp_extention_custom_link); ?>">
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Lọc đơn hàng theo SĐT', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_filter_order_by_phone" class="setting-value" value="<?php echo esc_attr($whp_extention_filter_order_by_phone); ?>" name="whp_extention_filter_order_by_phone" <?php echo esc_attr($whp_extention_filter_order_by_phone_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Bạn đã có thể lọc đơn hàng theo số điện thoại.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Cho phép upload SVG', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_extention_svg" class="setting-value" value="<?php echo esc_attr($whp_extention_svg); ?>" name="whp_extention_svg" <?php echo esc_attr($whp_extention_svg_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Bạn đã có thể upload SVG.', 'whp'); ?>
                        </p>
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