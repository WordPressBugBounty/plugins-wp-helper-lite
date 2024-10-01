<div id="mb-whp-contact" class="whp-contact <?php echo esc_attr($whp_contact_design_position_x); ?>">
    <div class="whp-contact-item" id="mb-whp-other">
        <div class="whp-contact-content">
            <div class="whp-contact-content-head">
                <?php
                if ($whp_contact_other_title) {
                    echo esc_html($whp_contact_other_title);
                } else {
                    echo esc_html('kênh liên hệ khác');
                }
                ?>
            </div>
            <div class="whp-contact-content-body">
                <?php if ($whp_contact_other_facebook) : ?>
                    <a target="_blank" class="whp-contact-content-item" href="<?php echo esc_url($whp_contact_other_facebook); ?>">
                        <img src="<?php echo esc_url(whp_get_image_url('facebook.png')); ?>" alt="Icon Facebook" class="whp-content-item-icon">
                        <span><?php echo __('Facebook', 'whp'); ?></span>
                    </a>
                <?php endif; ?>
                <?php if ($whp_contact_other_email) : ?>
                    <a target="_blank" class="whp-contact-content-item" href="<?php echo esc_url("mailto:" . $whp_contact_other_email); ?>">
                        <img src="<?php echo esc_url(whp_get_image_url('email.png')); ?>" alt="Icon Email" class="whp-content-item-icon">
                        <span><?php echo esc_html($whp_contact_other_email); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($whp_contact_other_facebook_page) : ?>
                    <a target="_blank" class="whp-contact-content-item" href="<?php echo esc_url("m.me/" . $whp_contact_other_facebook_page); ?>">
                        <img src="<?php echo esc_url(whp_get_image_url('messenger.png')); ?>" alt="Icon Messenger" class="whp-content-item-icon">
                        <span><?php echo __('Messenger', 'whp'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="whp-contact-button">
            <div class="whp-contact-icon">
                <?php echo esc_html(whp_get_icon('other')); ?>
                <div class="whp-contact-icon-close">
                    <?php echo esc_html(whp_get_icon('close')); ?>
                </div>
            </div>
            <div class="whp-contact-greeting">
                <?php
                if ($whp_contact_design_greeting) { ?>
                    <span> <?php echo esc_html($whp_contact_design_greeting); ?> </span> ;
                <?php
                } else { ?>
                    <span> <?php echo esc_html('Kênh liên hệ khác!'); ?> </span>
                <?php } ?>
                <div class="whp-contact-close-greeting">
                    <?php echo esc_html(whp_get_icon('close')); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="whp-contact-item" id="mb-whp-phone">
        <div class="whp-contact-content">
            <div class="whp-contact-content-head">
                <?php
                if ($whp_contact_phone_title) { ?>
                    <span> <?php echo esc_html($whp_contact_phone_title); ?> </span>
                <?php
                } else { ?>
                    <span> <?php echo esc_html('Gọi ngay cho chúng tôi!'); ?> </span>
                <?php } ?>
            </div>
            <div class="whp-contact-content-body">
                <?php
                if ($whp_contact_phone_data) : ?>
                    <?php foreach ($whp_contact_phone_data as $item) :
                        $avatar = $item['avatar'] ?? "contact-avata-women";
                        $avatar = "{$avatar}.svg";
                        $avatarUrl = whp_get_image_url($avatar);
                        $title = $item['title'] ?? "";
                        $phone = $item['phone'] ?? "";
                    ?>
                        <a target="_blank" class="whp-contact-content-item" style="z-index: 99999;" href="<?php echo esc_url("tel:" . $phone); ?>">
                            <img src="<?php echo esc_url($avatarUrl); ?>" alt="Icon Call" class="whp-content-item-icon">
                            <div class="whp-content-item-phone">
                                <h3 class="whp-content-item-title"> <?php echo esc_html($title); ?></h3>
                                <p><?php echo esc_html($phone); ?></p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="whp-contact-button">
            <div class="whp-contact-icon <?php echo esc_attr($whp_contact_phone_class); ?>" data-phone="<?php echo esc_attr($whp_contact_phone_only);  ?>">
                <?php echo esc_html(whp_get_icon('phone')); ?>
                <div class="whp-contact-icon-close">
                    <?php echo esc_html(whp_get_icon('close')); ?>
                </div>
            </div>
            <div class="whp-contact-greeting">

                <?php
                if ($whp_contact_phone_title) { ?>
                    <span> <?php echo esc_html($whp_contact_phone_title); ?> </span>
                <?php
                } else { ?>
                    <span> <?php echo esc_html('Gọi ngay cho chúng tôi!'); ?> </span>
                <?php } ?>
                <div class="whp-contact-close-greeting">
                    <?php echo esc_html(whp_get_icon('close')); ?>
                </div>
            </div>
        </div>
    </div>
</div>