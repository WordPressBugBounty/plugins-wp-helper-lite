<?php if (!defined('ABSPATH')) exit; ?>

<?php
$form_source_check = $whp_popup_form_source ?? 'email';
$form_id_check     = intval($whp_popup_form_id ?? 0);
$center_class      = ($form_source_check === 'form' && $form_id_check > 0) ? 'center whp-form-mode' : 'center';
?>
<div id="whp-popup" class="whp-hidden">
    <div class="whp-popup-background"></div>
    <div class="<?php echo esc_attr($center_class); ?>">
        <div class="modal-box">

            <div class="icon-close">×</div>

            <?php
            $form_source = $whp_popup_form_source ?? 'email';
            $form_id     = intval($whp_popup_form_id ?? 0);
            $is_form_mode = ($form_source === 'form' && $form_id > 0);
            ?>

            <?php if ($is_form_mode) :
                $form_post = get_post($form_id);
                if ($form_post) :
                    $shortcode = '';
                    switch ($form_post->post_type) {
                        case 'wpcf7_contact_form': $shortcode = '[contact-form-7 id="' . $form_id . '"]'; break;
                        case 'wpforms':            $shortcode = '[wpforms id="' . $form_id . '"]'; break;
                        case 'frm_form':           $shortcode = '[formidable id="' . $form_id . '"]'; break;
                        case 'fluentform':         $shortcode = '[fluentform id="' . $form_id . '"]'; break;
                    }
                    if ($shortcode) :
                        $display_title = !empty($whp_popup_title) ? $whp_popup_title : __('Đăng ký nhận ưu đãi', 'whp');
                        $display_sub   = !empty($whp_popup_sub_title) ? $whp_popup_sub_title : __('Nhận ngay ưu đãi độc quyền từ chúng tôi', 'whp');
                    ?>

                <div class="whp-popup-header">
                    <div class="whp-popup-header-icon">
                        <span class="fas fa-comments"></span>
                    </div>
                    <div class="whp-popup-header-body">
                        <h2><?php echo esc_html($display_title); ?></h2>
                        <p><?php echo esc_html($display_sub); ?></p>
                    </div>
                </div>

                <div class="whp-embedded-form">
                    <?php echo do_shortcode(wp_kses_post($shortcode)); ?>
                </div>

                    <?php endif;
                endif;

            else : ?>

                <div class="icon-letter-1">
                    <span class="fas fa-envelope"></span>
                </div>

                <header><?php echo esc_html(trim($whp_popup_title ?? '') ?: __('Đăng ký nhận ưu đãi', 'whp')); ?></header>
                <p><?php echo esc_html(trim($whp_popup_sub_title ?? '') ?: __('Nhận ngay ưu đãi độc quyền từ chúng tôi', 'whp')); ?></p>

                <form id="whp-form" method="POST">
                    <input type="email" name="email" required placeholder="<?php echo esc_attr__('Nhập email của bạn...', 'whp'); ?>">
                    <button type="submit" id="whp-button-popup">
                        <?php echo esc_html(trim($whp_popup_button ?? '') ?: __('Đăng ký ngay', 'whp')); ?>
                    </button>
                </form>

                <?php if ($whp_popup_facebook || $whp_popup_tiktok || $whp_popup_instagram || $whp_popup_youtube) : ?>
                <div class="icons">
                    <?php if ($whp_popup_facebook) : ?>
                        <a href="<?php echo esc_url($whp_popup_facebook); ?>" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($whp_popup_tiktok) : ?>
                        <a href="<?php echo esc_url($whp_popup_tiktok); ?>" target="_blank">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($whp_popup_youtube) : ?>
                        <a href="<?php echo esc_url($whp_popup_youtube); ?>" target="_blank">
                            <i class="fab fa-youtube"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ($whp_popup_instagram) : ?>
                        <a href="<?php echo esc_url($whp_popup_instagram); ?>" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>
