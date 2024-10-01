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
                        <?php echo esc_html(__('Họ và tên', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_payment_fullname" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_payment_fullname); ?>" name="whp_woocommerce_payment_fullname" <?php echo esc_attr($whp_woocommerce_payment_fullname_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Mẫu mặc định của trường này là sẽ tách riêng 2 ô Tên và Họ. Khi tính năng này được bật, Họ và tên chỉ còn lại 1 ô nhập.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Địa chỉ', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_payment_address" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_payment_address); ?>" name="whp_woocommerce_payment_address" <?php echo esc_attr($whp_woocommerce_payment_address_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __(' Mẫu <strong>mặc định</strong> của trường địa chỉ là <strong>có 2 ô nhập</strong> (Ô để nhập địa chỉ và ô để nhập tên, số căn hộ cho khách ở chung cư). Khi <strong>tính năng</strong> này được <strong>bật,</strong> trường địa chỉ <strong>còn lại 1 ô nhập.</strong>', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Ẩn quốc gia/Khu vực', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_payment_country" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_payment_country); ?>" name="whp_woocommerce_payment_country" <?php echo esc_attr($whp_woocommerce_payment_country_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Ẩn trường Quốc gia / Khu vực', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Ẩn tên công ty', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_payment_company" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_payment_company); ?>" name="whp_woocommerce_payment_company" <?php echo esc_attr($whp_woocommerce_payment_company_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Ẩn trường Tên công ty', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Ẩn mã bưu điện', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_payment_zipcode" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_payment_zipcode); ?>" name="whp_woocommerce_payment_zipcode" <?php echo esc_attr($whp_woocommerce_payment_zipcode_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Ẩn trường Mã bưu điện', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Tỉnh / Thành phố', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_payment_province" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_payment_province); ?>" name="whp_woocommerce_payment_province" <?php echo esc_attr($whp_woocommerce_payment_province_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Ẩn trường Tỉnh/ Thành phố', 'whp'); ?>
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