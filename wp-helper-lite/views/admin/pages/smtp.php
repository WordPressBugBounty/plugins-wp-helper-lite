<?php whp_get_shared('header'); ?>
<?php if ($isSubmit == 1) : ?>
    <div class="whp-notify">
        <?php echo __('Cập nhật cài đặt thành công', 'whp'); ?>
    </div>
<?php endif; ?>

<div class="whp-desc">
    <?php echo whp_show_html(__($itemInfo['desc'] ?? "", 'whp')) ?>
</div>
<?php $tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings'; ?>
<nav class="nav-tab-wrapper">
    <a href="?page=mb-wphelper-smtp&tab=settings" style="margin-left: 0px;" class="nav-tab  <?php if ($tab === 'settings') : ?>nav-tab-active<?php endif; ?>">Cấu hình</a>
    <a href="?page=mb-wphelper-smtp&tab=run" class="nav-tab <?php if ($tab === 'run') : ?>nav-tab-active<?php endif; ?>">Chạy thử nghiệm</a>

</nav>
<div class="tab-content">

    <?php switch ($tab):
        case 'settings': ?>
            <form method="post">
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
                                        <input type="checkbox" id="enable_contact" class="setting-value" value="<?php echo esc_attr($whp_smtp_active) ?>" name="whp_smtp_active" <?php echo esc_attr($whp_smtp_active_check); ?>>
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
                <div class="whp-setting <?php echo esc_attr($whp_smtp_active_check); ?>" id="security-setting">

                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Được gửi từ email', 'whp')); ?></label>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: support@gmail.com', 'whp')); ?>" name="whp_smtp_email" value="<?php if ($whp_smtp_email) {
                                                                                                                                                                                        echo esc_attr($whp_smtp_email);
                                                                                                                                                                                    } ?>">
                                    <p><?php echo __('Nếu bạn sử dụng Gmail, Yandex mail hoặc SMTP khác để gửi mail cho khách hàng thì đây sẽ là email gửi của bạn.', 'whp') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Tên người gửi', 'whp')); ?></label>
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: Tên Công ty/ Cá nhân', 'whp')); ?>" name="whp_smtp_from_name" value="<?php if ($whp_smtp_from_name) {
                                                                                                                                                                                                echo esc_attr($whp_smtp_from_name);
                                                                                                                                                                                            } ?>">
                                    <p>Tên được hiển thị cho email khi gửi.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Máy chủ SMTP', 'whp')); ?></label>
                                <select name="whp_smtp_host">
                                    <option value="smtp.gmail.com" <?php $whp_smtp_host == 'smtp.gmail.com' ? 'selected' : ''  ?>>smtp.gmail.com </option>
                                    <option value="smtp.office365.com" <?php $whp_smtp_host == 'smtp.office365.com' ? 'selected' : ''  ?>>smtp.office365.com</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Bảo mật SMTP', 'whp')); ?></label>
                                <div class="form-group">
                                    <select name="whp_smtp_security" id="" class="form-control">
                                        <?php
                                        $listSmtpSecurity = $data['listSmtpSecurity'] ?? [];
                                        foreach ($listSmtpSecurity as $itemSecurity) :
                                            $securityValue = $itemSecurity['value'] ?? "";
                                            $securityName = $itemSecurity['name'] ?? "";
                                            $securityPort = $itemSecurity['port'] ?? "";
                                            if ($securityValue == $whp_smtp_security) {

                                                $whp_smtp_security_select = 'selected';
                                            } else {
                                                $whp_smtp_security_select = '';
                                            }
                                        ?>
                                            <option value="<?php echo esc_attr($securityValue); ?>" <?php echo esc_attr($whp_smtp_security_select); ?> data-port="<?php echo esc_attr($securityPort); ?>">
                                                <?php echo esc_html($securityName); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Cổng SMTP', 'whp')); ?></label>
                                <div class="form-group">
                                    <input type="text" readonly placeholder="VD: 587/ 456/ 25" class="form-control" name="whp_smtp_port" value="<?php if ($whp_smtp_port) {
                                                                                                                                                    echo esc_attr($whp_smtp_port);
                                                                                                                                                } else {
                                                                                                                                                    echo esc_attr('587');
                                                                                                                                                } ?>">
                                    <p>Port 587 / 465 / 25</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Tên đăng nhập SMTP', 'whp')); ?></label>
                                <div class="form-group">
                                    <input type="text" placeholder="Tên email đăng ký smtp" class="form-control" name="whp_smtp_user" value="<?php if ($whp_smtp_user) {
                                                                                                                                                    echo esc_attr($whp_smtp_user);
                                                                                                                                                }  ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Mật khẩu SMTP', 'whp')); ?></label>
                                <div class="form-group" id="password-group">
                                    <input placeholder="Mật khẩu đăng ký smtp" type="password" class="form-control" name="whp_smtp_password" value="<?php if ($whp_smtp_password) {
                                                                                                                                                        echo esc_attr($whp_smtp_password);
                                                                                                                                                    }  ?>">
                                    <img src="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/view-svgrepo-com.svg"); ?>" class="btn-show-password" alt="">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="whp-buttons">
                    <div>
                        <?php submit_button(__('Lưu thông tin', 'whp')); ?>
                    </div>

                </div>
            </form>
        <?php break;

        default: ?>
            <form method="post">
                <div class="whp-setting">
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for=""><?php echo esc_html(__('Mail nhận thử', 'whp')); ?></label>
                                <div class="form-group">
                                    <input id="whp_smtp_email_receive" type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: support@gmail.com', 'whp')); ?>" name="whp_smtp_email_receive" value="">
                                    <p><?php echo __('Bạn vui lòng điền mail nhận nếu chạy thử.', 'whp') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="whp-setting-item">
                        <div class="whp-setting-content">
                            <div class="whp-setting-content-item">
                                <label for="">
                                    <?php echo esc_html(__('Nội dung ', 'whp')); ?>
                                </label>
                                <div class="form-group">
                                    <textarea placeholder="Nội dung mẫu" id="whp_smtp_email_content" name="whp_smtp_email_content" style="width: 500px;" id="" cols="10" rows="10"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="whp-buttons">
                    <div style="padding-top: 10px;float: right;">
                        <button class="button button-primary" id="test_mail" name="test_mail" value="1">
                            <p> Gửi </p>
                        </button>
                    </div>
                </div>
            </form>
    <?php break;
    endswitch; ?>
</div>
<?php whp_get_shared('footer'); ?>

<script>
    jQuery('#test_mail').on('click', function(e) {


        e.preventDefault();
        let wp_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
        let email = jQuery('#whp_smtp_email_receive').val();
        let content = jQuery('#whp_smtp_email_content').val();
        let nonce = '<?php echo wp_create_nonce('whp_smtp_send_mail_test_nonce'); ?>'; // Tạo nonce để bảo vệ AJAX
        let $button = jQuery(this);
        // Kiểm tra dữ liệu đầu vào trước khi gửi AJAX
        if (!email || !content) {
            alert('Vui lòng nhập email và nội dung.');
            return;
        }
        $button.prop('disabled', true).text('Đang gửi...');
        jQuery.ajax({
            type: "post",
            url: wp_ajax_url,
            data: {
                'action': 'whp_smtp_send_mail_test',
                'email': email,
                'content': content,
                'nonce': nonce
            },
            dataType: "json",
            success: function(res) {
                if (res['status'] == 200) {
                    alert('Bạn đã gửi mail thành công');
                } else {
                    alert('Bạn đã gửi mail thất bại: ' + res['message']);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Đã xảy ra lỗi khi gửi mail. Vui lòng thử lại.');
            },
            complete: function() {
                // Enable lại nút sau khi hoàn thành AJAX
                $button.prop('disabled', false).text('Gửi');
            }
        });
    });
</script>