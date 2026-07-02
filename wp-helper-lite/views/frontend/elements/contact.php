<?php
if (!defined('ABSPATH')) exit;

$has_phone = !empty($whp_contact_phone_data);
$show_zalo = ($whp_contact_other_zalo_active !== '0' && $whp_contact_other_zalo);
$show_fb = ($whp_contact_other_facebook_active !== '0' && $whp_contact_other_facebook);
$show_msg = ($whp_contact_other_messenger_active !== '0' && $whp_contact_other_facebook_page);
$show_email = ($whp_contact_other_email_active !== '0' && $whp_contact_other_email);
$has_other = ($show_zalo || $show_fb || $show_msg || $show_email);

// Luôn dùng layout V2
if (true) : ?>
<div id="mb-whp-contact" class="whp-contact <?php echo esc_attr($whp_contact_design_position_x); ?>">
    <div class="whp-contact-item" id="mb-whp-merged">

        <!-- Panel nổi -->
        <div class="whp-contact-content whp-v2-panel">

            <!-- Header xanh -->
            <div class="whp-v2-header">
                <div class="whp-v2-header-left">
                    <div class="whp-v2-header-icon">
                        <svg width="30" height="30" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Headset band -->
                            <path d="M32 6C17.6 6 6 17.6 6 32c0 1 .8 1.8 1.8 1.8s1.8-.8 1.8-1.8C9.6 19.3 19.7 9.2 32 9.2s22.4 10.1 22.4 22.8c0 1 .8 1.8 1.8 1.8s1.8-.8 1.8-1.8C58 17.6 46.4 6 32 6Z" fill="currentColor"/>
                            <!-- Main bubble -->
                            <path d="M32 12c-11.6 0-21 9.4-21 21c0 5 1.8 9.7 5 13.4l-1.9 6.2c-.3 1 .7 1.9 1.7 1.6l6.8-2.6c2.9 1.6 6.1 2.4 9.4 2.4c11.6 0 21-9.4 21-21S43.6 12 32 12Z" fill="currentColor"/>
                            <!-- Eyes -->
                            <circle cx="25" cy="32" r="3" fill="#fff"/>
                            <circle cx="39" cy="32" r="3" fill="#fff"/>
                            <!-- Mouth/Smile -->
                            <path d="M25 39c2.5 3.5 11.5 3.5 14 0" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
                            <!-- Ear pads -->
                            <rect x="4" y="27" width="5" height="10" rx="2.5" fill="currentColor"/>
                            <rect x="55" y="27" width="5" height="10" rx="2.5" fill="currentColor"/>
                            <!-- Mic arm -->
                            <path d="M55 35c0 6-8 10-12 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" fill="none"/>
                        </svg>
                    </div>
                    <div class="whp-v2-header-text">
                        <strong><?php echo esc_html($whp_contact_phone_title ?: 'Hỗ trợ trực tuyến'); ?></strong>
                        <span><?php echo esc_html($whp_contact_design_greeting ?: 'Chúng tôi luôn sẵn sàng hỗ trợ bạn'); ?></span>
                    </div>
                </div>
                <?php 
                $is_status_off = ($whp_contact_online_status_text === 'Đang off');
                $badge_style = $is_status_off ? 'background: rgba(239, 68, 68, 0.15); color: #ef4444;' : '';
                $dot_style = $is_status_off ? 'background: #ef4444;' : '';
                ?>
                <div class="whp-v2-online-badge" style="<?php echo $badge_style; ?>">
                    <span class="whp-v2-dot" style="<?php echo $dot_style; ?>"></span><?php echo esc_html($whp_contact_online_status_text ?: 'Đang online'); ?>
                </div>
            </div>

            <!-- Body -->
            <div class="whp-v2-body">

                <!-- Nhóm avatar / support team illustration -->
                <div class="whp-v2-illustration-container">
                    <img src="<?php echo esc_url(whp_get_image_url('support-team.png')); ?>" class="whp-v2-illustration" alt="Support Team">
                </div>

                <!-- Tiêu đề chào -->
                <h3 class="whp-v2-welcome">Chào bạn, chúng tôi có thể giúp gì?</h3>
                <p class="whp-v2-sub">Chọn kênh liên hệ phù hợp để được hỗ trợ nhanh nhất</p>

                <!-- Nút Gọi Hotline lớn -->
                <?php if ($whp_contact_phone_data_number > 1) : ?>
                    <button type="button" class="whp-v2-call-btn whp-toggle-phones-btn">
                        <span class="whp-v2-call-icon"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.47 11.47 0 003.58.57 1 1 0 011 1V21a1 1 0 01-1 1A17 17 0 013 5a1 1 0 011-1h3.5a1 1 0 011 1 11.47 11.47 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg></span>
                        <span class="whp-v2-call-label"><?php echo esc_html($whp_contact_phone_btn_text ?: 'GỌI HOTLINE'); ?></span>
                        <svg class="whp-v2-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                    </button>
                    <div class="whp-agents-collapse" style="display:none;">
                        <div class="whp-agents-collapse-inner">
                            <?php foreach ((array)$whp_contact_phone_data as $item) :
                                $av    = $item['avatar'] ?? 'contact-avata-women';
                                $title = $item['title']  ?? '';
                                $phone = $item['phone']  ?? '';
                            ?>
                                <a href="tel:<?php echo esc_attr($phone); ?>" class="whp-agent-row">
                                    <img src="<?php echo esc_url(whp_get_image_url("{$av}.svg")); ?>" alt="Avatar">
                                    <div class="whp-agent-info">
                                        <strong><?php echo esc_html($title); ?></strong>
                                        <span><?php echo esc_html($phone); ?></span>
                                    </div>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="13" height="13"><polyline points="9 18 15 12 9 6"/></svg>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif ($whp_contact_phone_only) : ?>
                    <a href="tel:<?php echo esc_attr($whp_contact_phone_only); ?>" class="whp-v2-call-btn">
                        <span class="whp-v2-call-icon"><svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.01-.24 11.47 11.47 0 003.58.57 1 1 0 011 1V21a1 1 0 01-1 1A17 17 0 013 5a1 1 0 011-1h3.5a1 1 0 011 1 11.47 11.47 0 00.57 3.58 1 1 0 01-.25 1.01l-2.2 2.2z"/></svg></span>
                        <span class="whp-v2-call-label"><?php echo esc_html($whp_contact_phone_btn_text ?: 'GỌI HOTLINE'); ?></span>
                        <svg class="whp-v2-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14" height="14"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                <?php endif; ?>

                <p class="whp-v2-cta-note">
                    <svg viewBox="0 0 24 24" fill="#22c55e" width="15" height="15" style="vertical-align:middle;margin-right:4px;flex-shrink:0;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5-4-4 1.41-1.41L10 13.67l6.59-6.58L18 8.5l-8 8z"/></svg>
                    <?php 
                    if ($whp_contact_online_status_text === 'Đang off') {
                        echo esc_html($whp_contact_phone_cta_note_offline ?: 'Hiện tại ngoại tuyến');
                    } else {
                        echo esc_html($whp_contact_phone_cta_note ?: 'Hỗ trợ nhanh chóng 24/7');
                    }
                    ?>
                </p>

                <!-- Divider -->
                <?php if ($has_other) : ?>
                <div class="whp-v2-divider"><span>Hoặc liên hệ qua kênh</span></div>
                <?php endif; ?>

                <!-- Social cards dạng lưới -->
                <div class="whp-v2-social-grid">
                    <?php if ($show_zalo) : ?>
                    <a href="<?php echo esc_url('https://zalo.me/' . $whp_contact_other_zalo); ?>" target="_blank" class="whp-v2-social-card">
                        <div class="whp-v2-social-icon zalo">
                            <img src="<?php echo esc_url(whp_get_image_url('zalo.png')); ?>" alt="Zalo">
                        </div>
                        <strong>Zalo</strong>
                        <span>Nhắn Zalo</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($show_msg) : ?>
                    <a href="<?php echo esc_url('https://m.me/' . $whp_contact_other_facebook_page); ?>" target="_blank" class="whp-v2-social-card">
                        <div class="whp-v2-social-icon messenger">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M12 2C6.477 2 2 6.145 2 11.259c0 2.88 1.418 5.45 3.636 7.17v3.52l3.33-1.835c.89.247 1.833.38 2.804.38 5.523 0 10-4.145 10-9.27S17.523 2 12 2zm1.03 12.477-2.545-2.715-4.969 2.715 5.467-5.802 2.607 2.715 4.908-2.715-5.468 5.802z"/></svg>
                        </div>
                        <strong>Messenger</strong>
                        <span>Chat ngay</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($show_fb) : ?>
                    <a href="<?php echo esc_url($whp_contact_other_facebook); ?>" target="_blank" class="whp-v2-social-card">
                        <div class="whp-v2-social-icon facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M24 12.073C24 5.404 18.627 0 12 0S0 5.404 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.791-4.697 4.533-4.697 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.269h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>
                        </div>
                        <strong>Facebook</strong>
                        <span>Ghé Fanpage</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($show_email) : ?>
                    <a href="mailto:<?php echo esc_attr($whp_contact_other_email); ?>" class="whp-v2-social-card">
                        <div class="whp-v2-social-icon email">
                            <svg viewBox="0 0 24 24" fill="currentColor" width="22" height="22"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        </div>
                        <strong>Email</strong>
                        <span>Gửi thư</span>
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Footer note -->
                <p class="whp-v2-footer-note">
                    <svg viewBox="0 0 24 24" fill="#22c55e" width="15" height="15" style="vertical-align:middle;margin-right:4px;flex-shrink:0;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5-4-4 1.41-1.41L10 13.67l6.59-6.58L18 8.5l-8 8z"/></svg>
                    <span>Đội ngũ của chúng tôi sẽ phản hồi bạn trong thời gian sớm nhất! 👋</span>
                </p>

            </div><!-- /.whp-v2-body -->
        </div><!-- /.whp-v2-panel -->

        <!-- Nút nổi FAB -->
        <div class="whp-contact-button">
            <div class="whp-contact-icon">
                <?php whp_get_icon('other'); ?>
                <div class="whp-contact-icon-close">
                    <?php whp_get_icon('close'); ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php endif; ?>
