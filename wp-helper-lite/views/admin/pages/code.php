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
    <div class="whp-setting" id="code-setting">
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Header Scripts', 'whp')); ?></label>
                    <div class="form-group">
                        <textarea name="whp_code_header" id="" rows="10" class="form-control"><?php echo stripslashes($whp_code_header); ?></textarea>
                        <?php echo __('<p>Đoạn mã này sẽ được đặt vào <code>&lt;head&gt;</code> trang.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Body Scripts - Top', 'whp')); ?></label>
                    <div class="form-group">
                        <textarea name="whp_code_body" id="" rows="10" class="form-control"><?php echo stripslashes($whp_code_body); ?></textarea>
                        <?php echo __(' <p>Đoạn mã này sẽ được đặt vào <code>&lt;body&gt;</code> trang.</p>', 'whp'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Footer Scripts', 'whp')); ?></label>
                    <div class="form-group">
                        <textarea name="whp_code_footer" id="" rows="10" class="form-control"><?php echo stripslashes($whp_code_footer); ?></textarea>
                        <?php echo __(' <p>Đoạn mã này sẽ được đặt vào <code>&lt;Footer&gt;</code> trang.</p>', 'whp') ?>
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