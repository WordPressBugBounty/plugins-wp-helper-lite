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
    <div class="whp-setting ecommerce-setting wallet-setting" id="security-setting">
        <?php
        $listWallet = $data['listWallet'] ?? [];
        ?>
        <?php foreach ($listWallet as $keyList => $itemList) :
            $itemId = "whp_woocommerce_wallet_{$keyList}";
            $itemImgUrl = $itemList['url'] ?? "";
            $itemDesc = $itemList['desc'] ?? "";
            $itemTitle = $itemList['title'] ?? "";
            $itemCheck = $$itemId == '1' ? "checked" : "no_checked";
        ?>
            <div class="whp-setting-item">
                <div class="whp-setting-content">
                    <div class="whp-setting-content-item">
                        <label for="<?php echo esc_attr($itemId); ?>">
                            <p><?php echo esc_html($itemTitle); ?></p>
                            <div class="whp-setting-content-item-label-group">
                                <img src="<?php echo esc_url($itemImgUrl); ?>" class="img-responsive">
                                <p><?php echo whp_show_html($itemDesc); ?></p>
                            </div>

                        </label>
                        <div class="form-group">
                            <label class="switch">
                                <input type="checkbox" id="<?php echo esc_attr($itemId); ?>" class="setting-value" value="<?php echo esc_attr($$itemId); ?>" name="<?php echo esc_attr($itemId); ?>" <?php echo esc_attr($itemCheck) ?>>
                                <div class="slider round">
                                    <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                    <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="whp-buttons">
        <?php submit_button(__('Lưu thông tin', 'whp')); ?>
    </div>
</form>
<?php whp_get_shared('footer'); ?>