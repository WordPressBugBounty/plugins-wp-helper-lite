<?php whp_get_shared('header'); ?>
<?php if ($isSubmit == 1) : ?>
    <div class="whp-notify">
        <?php echo __('Cập nhật cài đặt thành công', 'whp'); ?>
    </div>
<?php endif; ?>
<div class="whp-desc">
    <?php echo esc_html(__($itemInfo['desc'] ?? "", 'whp')) ?>
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
                            <input type="checkbox" id="enable_contact" class="setting-value" value="<?php echo esc_attr($whp_contact_active) ?>" name="whp_contact_active" <?php echo esc_attr($whp_contact_active_check); ?>>
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
    <div class="whp-setting <?php echo esc_attr($whp_contact_active_check); ?>" id="contact-setting">
        <div class="whp-setting-item">
            <div class="whp-setting-header">
                <h3 class="whp-setting-title">
                    <?php echo esc_html(__('1. Nút trò chuyện', 'whp')); ?>
                </h3>
                <p>
                    <?php echo esc_html(__('Bằng việc thay đổi màu sắc, nội dung và vị trí, sẽ thu hút khách hàng tốt hơn dẫn đến việc tương tác nhiều hơn.', 'whp')); ?>
                </p>
            </div>
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Chọn màu', 'whp')); ?></label>
                    <input type="color" name="whp_contact_design_color" value="<?php echo esc_attr($whp_contact_design_color); ?>">
                </div>
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề', 'whp')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Xin chào! Chúng tôi có thể giúp gì cho bạn?', 'whp')); ?>" name="whp_contact_design_greeting" value="<?php echo esc_attr($whp_contact_design_greeting); ?>">
                </div>
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Độ cao hiển thị (%)', 'whp')); ?></label>
                    <input type="number" class="form-control" name="whp_contact_design_position_y" value="<?php echo esc_attr($whp_contact_design_position_y); ?>">
                </div>
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Vị trí hiển thị', 'whp')); ?></label>
                    <select name="whp_contact_design_position_x" id="" class="form-control">
                        <?php
                        $listPosition = $data['listPosition'] ?? "";
                        foreach ($listPosition as $itemList) :
                            $itemValue = $itemList['value'] ?? "";
                            $itemName = $itemList['name'] ?? "";
                            $itemSelect = $itemValue == $whp_contact_design_position_x ? "selected" : "";
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
            <div class="whp-setting-header">
                <h3 class="whp-setting-title">
                    <?php echo esc_html(__('2. Nút gọi điện', 'whp')); ?>
                </h3>
                <p>
                    <?php echo esc_html(__('Việc hiển thị nút gọi điện khi bấm vào là số điện thoại của tư vấn viên hoặc nhân viên hỗ trợ sẽ giúp khách hàng của bạn có thể tự liên hệ nhanh chóng và hiệu quả hơn.', 'whp')); ?>
                </p>
            </div>
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề', 'whp')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Gọi cho chúng tôi ngay', 'whp')); ?>" name="whp_contact_phone_title" value="<?php echo esc_attr($whp_contact_phone_title); ?>">
                </div>
                <div class="whp-setting-content-item w-100 mb-0">
                    <label for=""><?php echo esc_html(__('Bạn cần thêm thông tin nhân viên tư vấn hoặc hỗ trợ', 'whp')); ?></label>
                    <div></div>
                </div>
                <div class="whp-setting-content-item contact-info">
                    <div class="contact-list">
                        <?php

                        $phoneDataNumber = $whp_contact_phone_data ?? 0;
                        if ($whp_contact_phone_data) : ?>
                            <?php $index = 1;
                            foreach ($whp_contact_phone_data as $key => $phoneDataItem) :
                                $index++;
                                $keyOption = "whp_contact_phone_data";
                                $avatar = $phoneDataItem['mbwp-contact-avata'] ?? $phoneDataItem['avatar'];
                                $title = $phoneDataItem['mbwp-contact-title'] ?? $phoneDataItem['title'];
                                $phone =  $phoneDataItem['mbwp-contact-phone-number'] ?? $phoneDataItem['phone'];
                                $avatarWoman = $avatar == 'contact-avata-women' ? 'checked' : "";
                                $avatarMen = $avatar == 'contact-avata-men' ? 'checked' : "";
                                $avatarSupport = $avatar == 'contact-avata-support' ? 'checked' : "";
                            ?>
                                <div class="contact-list-item" data-id="<?php echo esc_attr($index); ?>">
                                    <button type="button" class='contact-remove'><img src='<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/"); ?>remove.png'></button>
                                    <div class="form-group">
                                        <label for="">Hình đại diện</label>
                                        <div class="form-avatar-group">
                                            <div class="form-avatar-item">
                                                <label for="avatar_<?php echo esc_attr($index); ?>_nu">
                                                    <img src="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/"); ?>nu.svg">
                                                </label>
                                                <input type="radio" name="<?php echo esc_attr($keyOption); ?>[<?php echo esc_attr($index); ?>][avatar]" value="contact-avata-women" id="avatar_<?php echo esc_attr($index); ?>_nu" <?php echo esc_attr($avatarWoman); ?>>
                                                <?php echo __('Nữ', 'whp'); ?>
                                            </div>
                                            <div class="form-avatar-item">
                                                <label for="avatar_<?php echo esc_attr($index); ?>_nam">
                                                    <img src="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/"); ?>nam.svg">
                                                </label>
                                                <input type="radio" name="<?php echo esc_attr($keyOption); ?>[<?php echo esc_attr($index); ?>][avatar]" value="contact-avata-men" id="avatar_<?php echo esc_attr($index); ?>_nam" <?php echo esc_attr($avatarMen); ?>>
                                                <?php echo __('Nam', 'whp'); ?>
                                            </div>
                                            <div class="form-avatar-item">
                                                <label for="avatar_<?php echo esc_attr($index); ?>_support">
                                                    <img src="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/"); ?>24.svg">
                                                </label>
                                                <input type="radio" name="<?php echo esc_attr($keyOption); ?>[<?php echo esc_attr($index); ?>][avatar]" value="contact-avata-support" id="avatar_<?php echo esc_attr($index); ?>_support" <?php echo esc_attr($avatarSupport); ?>>
                                                <?php echo __('Support 24/7', 'whp'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Tên hiển thị</label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Nhập tên hiển thị" name="<?php echo esc_attr($keyOption); ?>[<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($title); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="">Số điện thoại</label>
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="Nhập số điện thoại hiển thị" name="<?php echo esc_attr($keyOption); ?>[<?php echo esc_attr($index); ?>][phone]" value="<?php echo esc_attr($phone); ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="whp-setting-content-item">
                    <button type="button" href="#" class="btn btn-primary-outline" id="btnAddMorePhone" data-image-url="<?php echo esc_url(MB_WHP_URL . "/assets/admin/images/"); ?>" data-number="<?php echo esc_attr($phoneDataNumber); ?>">
                        <?php echo __('Thêm nhân viên', 'whp'); ?>
                    </button>
                </div>
            </div>
        </div>
        <div class="whp-setting-item">
            <div class="whp-setting-header">
                <h3 class="whp-setting-title">
                    <?php echo esc_html(__('3. Các kênh khác', 'whp')); ?>
                </h3>
                <p>
                    <?php echo esc_html(__('Bạn có thể sử dụng các kênh liên hệ khác như email, các mạng xã hội như: Facebook, Zalo, để trao đổi và tư vấn với khách hàng dễ dàng hơn.', 'whp')); ?>
                </p>
            </div>
            <div class="whp-setting-content">
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Tiêu đề', 'whp')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Gọi ngay cho chúng tôi', 'whp')); ?>" name="whp_contact_other_title" value="<?php echo esc_attr($whp_contact_other_title); ?>">
                </div>
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Địa chỉ Email', 'whp')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Ví dụ: example@gmail.com', 'whp')); ?>" name="whp_contact_other_email" value="<?php echo esc_attr($whp_contact_other_email); ?>">
                </div>
                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Facebook', 'whp')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Vd: https://www.facebook.com/your-page', 'whp')); ?>" name="whp_contact_other_facebook" value="<?php echo esc_attr($whp_contact_other_facebook); ?>">
                </div>

                <div class="whp-setting-content-item">
                    <label for=""><?php echo esc_html(__('Facebook chat trực tuyến', 'whp')); ?></label>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="<?php echo esc_attr(__('Nhập Page ID ứng dụng', 'whp')); ?>" name="whp_contact_other_facebook_page" value="<?php echo esc_attr($whp_contact_other_facebook_page); ?>">
                        <p><?php echo __("Xem hướng dẫn nhận Page ID từ Facebook <a href = 'https://wiki.matbao.net/kb/huong-dan-lay-id-fanpage-facebook/' target = '_blank'>tại đây</a>", 'whp'); ?>
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