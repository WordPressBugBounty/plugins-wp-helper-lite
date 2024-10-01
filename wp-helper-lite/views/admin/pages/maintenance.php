<?php whp_get_shared('header'); ?>
<?php if ($isSubmit == 1) : ?>
    <div class="whp-notify">
        <?php echo __('Cập nhật cài đặt thành công', 'whp'); ?>
    </div>
<?php endif; ?>
<div class="whp-desc">
    <?php echo whp_show_html(__($itemInfo['desc'] ?? "", 'whp')) ?>
</div>
<form method="post" id="form-maintenance">
    <?php wp_nonce_field('_token', '_token'); ?>
    <div class="whp-setting">
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Kích hoạt', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="enable_maintenance" class="setting-value" value="<?php echo esc_attr($whp_maintenance_active) ?>" name="whp_maintenance_active" <?php echo $whp_maintenance_active_check ?>>
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
    <!-- <div class="whp-setting <?php echo esc_attr($whp_maintenance_active_check); ?>" id="maintenance-setting">
        <div class="whp-setting-item">
            <div class="whp-setting-content-item">
                <label for="">
                    <?php echo esc_html(__('Banner', 'whp')); ?>
                </label>
                <div class="form-group">
                    <button class="btn btn-upload-logo " type="button" id="uploadLogo"><?php echo __('Upload Banner', 'whp'); ?></button>
                    <div class="preview-group">

                        <img src="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/remove.png"); ?>" class="img-responsive preview-close" data-default="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/placeholder-image.jpg"); ?>">
                        <img src="<?php echo esc_url($whp_maintenance_banner); ?>" class="img-responsive preview-logo">
                    </div>
                    <input type="hidden" name="whp_maintenance_banner" value="<?php echo esc_attr($whp_maintenance_banner); ?>">
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề trang', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: Maintenance', 'whp')); ?>" name="whp_maintenance_title" value="<?= $whp_maintenance_title ?? ''  ?>">
                        <p><?php echo __('', 'whp') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: Rất tiết', 'whp')); ?>" name="whp_maintenance_heading" value="<?php if ($whp_maintenance_heading) {
                                                                                                                                                                            echo esc_attr($whp_maintenance_heading);
                                                                                                                                                                        } ?>">

                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề phụ', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: Website hiện đang bảo trì', 'whp')); ?>" name="whp_maintenance_heading_sub" value="<?= $whp_maintenance_heading_sub ?? '' ?>">

                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Mô tả', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: Chúng tôi hiện đang nâng cấp, bạn ghé vào sau nhé.', 'whp')); ?>" name="whp_maintenance_desc" value="<?= $whp_maintenance_desc ?? '' ?>">
                    </div>
                </div>
            </div>
        </div>
    </div> -->
    <div class="whp-buttons">
        <div style="display: flex;justify-content: space-between;">
            <?php submit_button(__('Lưu thông tin', 'whp')); ?>
            <!-- <p class="submit">
                <a href="<?php echo esc_url('/maintenance-preview')  ?>" class="button button-primary  <?php if (!$whp_maintenance_active)  echo esc_attr('whp-disable'); ?>" target="_blank">Xem thử</a>
            </p> -->
        </div>
    </div>
</form>
<?php whp_get_shared('footer'); ?>