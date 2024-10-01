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
                        <?php echo esc_html(__('Thay đổi nội dung  nút mua hàng', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="<?php echo __('Thêm vào giỏ hàng', 'whp');  ?>" name="whp_woocommerce_cta_text" value="<?php echo esc_attr($whp_woocommerce_cta_text); ?>">
                        <p>
                            <?php echo __('Bằng việc thay đổi nội dung và cài đặt khác của nút mua hàng sẽ thu hút khách hàng tốt hơn dẫn đến việc bán hàng của bạn sẽ hiệu quả hơn.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Chuyển giá 0đ thành liên hệ', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_cta_convert_zero_to_contact" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_cta_convert_zero_to_contact); ?>" name="whp_woocommerce_cta_convert_zero_to_contact" <?php echo esc_attr($whp_woocommerce_cta_convert_zero_to_contact_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Khi sử dụng tính năng này, [nút mua hàng] của những sản phẩm bạn đã cài đặt giá “0đ” sẽ được đổi thành [nút liên hệ].', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Thêm nút mua hàng ngay', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_cta_show_buynow_button" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_cta_show_buynow_button); ?>" name="whp_woocommerce_cta_show_buynow_button" <?php echo esc_attr($whp_woocommerce_cta_show_buynow_button_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Hiển thị nút mua hàng ngay tại trang chi tiết sản phẩm. Giúp khách hàng thực hiện thao tác thanh toán một cách nhanh chóng.', 'whp'); ?>
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