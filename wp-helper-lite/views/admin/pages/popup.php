<?php whp_get_shared('header'); ?>

<?php if ($isSubmit == 1) : ?>
    <div class="whp-notify">
        <?php echo __('Cập nhật cài đặt thành công', 'whp'); ?>
    </div>
<?php endif; ?>
<div class="whp-desc">
    <?php echo esc_html(__($itemInfo['desc'] != 0 ?? "", 'whp')) ?>
</div>
<form method="post">
    <?php wp_nonce_field('_token', '_token'); ?>
    <div class="whp-setting">
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Kích hoạt dịch vụ', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="enable_popup" class="setting-value" value="<?php echo esc_attr($whp_popup_active) ?>" name="whp_popup_active" <?php echo esc_attr($whp_popup_active_check); ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="whp-setting <?php
                            echo esc_attr($whp_popup_active_check); ?>" id="popup-setting">
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Kiểu pop-up ', 'whp')); ?></label>
                    <div class="form-group">
                        <select class="form-control" name="whp_popup_type">
                            <?php
                            foreach ($data['type'] as $key => $item) { ?>
                                <option <?=
                                        $whp_popup_type == $item['value'] ? 'selected' : ''  ?> value="<?php echo esc_attr($item['value']) ?>"><?php echo esc_attr($item['name']) ?> </option>
                            <?php  } ?>
                        </select>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="whp-setting <?= $whp_popup_active == 0 || $whp_popup_type != 0 ? 'no_checked' : '' ?>" id="whp-newsletter">
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề', 'whp')); ?></label>
                    <div class="form-group">
                        <input value="<?= isset($whp_popup_title) ? $whp_popup_title : '' ?>" type="text" placeholder="" class="form-control" name="whp_popup_title">
                        <?php echo __('<p>Tiêu đề chính của pop-up</p>', 'whp'); ?>
                    </div>

                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề phụ', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" value="<?= isset($whp_popup_sub_title) ? $whp_popup_sub_title : '' ?>" placeholder="" class="form-control" name="whp_popup_sub_title">
                        <?php echo __('<p>Tiêu đề phụ của trang pop-up.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Button', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" value="<?= isset($whp_popup_button) ? $whp_popup_button : '' ?>" placeholder="" class="form-control" name="whp_popup_button">
                        <?php echo __('<p>Tiêu đề nút gửi.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Facebook', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" value="<?= isset($whp_popup_facebook) ? $whp_popup_facebook : '' ?>" placeholder="" class="form-control" name="whp_popup_facebook">
                        <?php echo __('<p>Đường dẫn trang cá nhân facebook.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiktok', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" value="<?= isset($whp_popup_tiktok) ? $whp_popup_tiktok : '' ?>" placeholder="" class="form-control" name="whp_popup_tiktok">
                        <?php echo __('<p>Đường dẫn trang cá nhân tiktok.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Youtube', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" value="<?= isset($whp_popup_youtube) ? $whp_popup_youtube : '' ?>" placeholder="" class="form-control" name="whp_popup_youtube">
                        <?php echo __('<p>Đường dẫn trang cá nhân youtuve.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Instagram', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" value="<?= isset($whp_popup_instagram) ? $whp_popup_instagram : '' ?>" placeholder="" class="form-control" name="whp_popup_instagram">
                        <?php echo __('<p>Đường dẫn trang cá nhân instagram.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Mail template', 'whp')); ?></label>
                    <div class="form-group">

                        <?php wp_editor(empty($whp_popup_mail_template) ? '<h5>Chào admin,</h5>
Bạn vừa có người dùng đăng ký nhận tin với email : {email}
<h5>Thân chào</h5>' : $whp_popup_mail_template, 'editor-mail-template', $settings = array('textarea_name' => 'whp_popup_mail_template')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="whp-setting <?= $whp_popup_active == 0 || $whp_popup_type != 1 ? 'no_checked' : '' ?>" id="whp-banner">
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Banner', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <button class="btn btn-upload-logo " type="button" id="uploadLogo"><?php echo __('Upload Banner', 'whp'); ?></button>
                        <div class="preview-group">

                            <img src="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/remove.png"); ?>" class="img-responsive preview-close" data-default="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/placeholder-image.jpg"); ?>">
                            <img src="<?php echo esc_url($whp_popup_image_banner); ?>" class="img-responsive preview-logo">
                        </div>
                        <input type="hidden" name="whp_popup_image_banner" value="<?php echo esc_attr($whp_popup_image_banner); ?>">
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Link chuyển hướng', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <input class="form-control" type="text" value="<?= $whp_popup_link_redirect ?? '' ?>" name="whp_popup_link_redirect">
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