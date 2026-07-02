<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>
<?php whp_get_shared('header'); ?>
<?php if ($isSubmit == 1) : ?>
    <div class="mb-wph-notify">
        <?php echo __('Cập nhật cài đặt thành công', 'whp'); ?>
    </div>
<?php endif; ?>
<div class="mb-wph-desc">
    <?php echo whp_show_html(__($itemInfo['desc'] ?? "", 'whp')) ?>
</div>
<form method="post">
    <?php wp_nonce_field('_token', '_token'); ?>
    <div class="mb-setting">
        <div class="mb-setting-item">
            <div class="mb-setting-content">
                <div class="mb-setting-content-item">
                    <label for=""><?php echo esc_html(__('Mobile', 'whp')); ?></label>
                    <textarea id="css-mobile" name="whp_reponsive_mobile"><?= $whp_reponsive_mobile ?></textarea>
                </div>
            </div>
        </div>
        <div class="mb-setting-item">
            <div class="mb-setting-content">
                <div class="mb-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tablet', 'whp')); ?></label>
                    <textarea id="css-tablet" name="whp_reponsive_table"> <?= $whp_reponsive_tablet ?></textarea>
                </div>
            </div>
        </div>
        <div class="mb-setting-item">
            <div class="mb-setting-content">
                <div class="mb-setting-content-item">
                    <label for=""><?php echo esc_html(__('Desktop', 'whp')); ?></label>
                    <textarea id="css-desktop" name="whp_reponsive_desktop"> <?= $whp_reponsive_desktop ?></textarea>
                </div>
            </div>
        </div>
    </div>
    <div class="mb-buttons">
        <?php submit_button(__('Lưu thông tin', 'whp')); ?>
    </div>
</form>
<?php whp_get_shared('footer'); ?>