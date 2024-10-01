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
                        <?php echo esc_html(__('Tạo thông báo mua hàng', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_advance_enable_notice" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_advance_enable_notice); ?>" name="whp_woocommerce_advance_enable_notice" <?php echo esc_attr($whp_woocommerce_advance_enable_notice_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Tính năng này sẽ <strong>tự tạo 1 thông báo mua hàng</strong> của bạn với <strong>nội dung vừa có người vừa mua sản phẩm này.</strong> Mục đích là để khách hàng quyết định mua hàng nhanh hơn.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Xuất hóa đơn VAT', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_advance_enable_vat" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_advance_enable_vat); ?>" name="whp_woocommerce_advance_enable_vat" <?php echo esc_attr($whp_woocommerce_advance_enable_vat_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Tính năng này sẽ <strong>thêm trường yêu cầu xuất hóa đơn VAT vào Woocommerce</strong> của bạn.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Rút gọn mô tả sản phẩm', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_advance_enable_compact_desc" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_advance_enable_compact_desc); ?>" name="whp_woocommerce_advance_enable_compact_desc" <?php echo esc_attr($whp_woocommerce_advance_enable_compact_desc_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Đôi khi mô tả sản phẩm của bạn quá dài, khiến cho quá trình xem sản phẩm khó khăn. Tính năng này sẽ cho mô tả sản phẩm của bạn gọn hơn.', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for="">
                        <?php echo esc_html(__('Thông báo đơn hàng về Telegram', 'whp')); ?>
                    </label>
                    <div class="form-group">
                        <label class="switch">
                            <input type="checkbox" id="whp_woocommerce_advance_enable_notify_telegram" class="setting-value" value="<?php echo esc_attr($whp_woocommerce_advance_enable_notify_telegram); ?>" name="whp_woocommerce_advance_enable_notify_telegram" <?php echo esc_attr($whp_woocommerce_advance_enable_notify_telegram_check) ?>>
                            <div class="slider round">
                                <span class="on"> <?php echo esc_html(__('Bật', 'whp')); ?></span>
                                <span class="off"> <?php echo esc_html(__('Tắt', 'whp')); ?></span>
                            </div>
                        </label>
                        <p>
                            <?php echo __('Kích hoạt chức năng này sẽ gửi thông báo đơn hàng của bạn về ứng dụng Telegram của bạn. Hướng dẫn cài đặt thông báo Telegram. <a href="https://wiki.matbao.net/kb/huong-dan-tao-bot-va-gui-thong-bao-telegram/" target="_blank">Xem hướng dẫn tại đây</a>', 'whp'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div id="telegram_contact" class="whp-setting <?= esc_attr($whp_woocommerce_advance_enable_notify_telegram_check) ?>">
            <div class="whp-setting-item ">
                <div class="whp-setting-content">
                    <div class="whp-setting-content-item">
                        <label for="">
                            <?php echo esc_html(__('Telegram Bot Token', 'whp')); ?>
                        </label>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="<?php echo __('Nhập Token của Bot Telegram', 'whp');  ?>" name="whp_woocommerce_advance_telegram_token" value="<?php echo esc_attr($whp_woocommerce_advance_telegram_token); ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="whp-setting-item">
                <div class="whp-setting-content">
                    <div class="whp-setting-content-item">
                        <label for="">
                            <?php echo esc_html(__('Telegram Chat ID', 'whp')); ?>
                        </label>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="<?php echo __('Nhập Chat ID của Bot Telegram', 'whp');  ?>" name="whp_woocommerce_advance_telegram_chatid" value="<?php echo esc_attr($whp_woocommerce_advance_telegram_chatid); ?>">
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